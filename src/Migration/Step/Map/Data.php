<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\StageInterface;
use Migration\Config;
use Migration\Reader\MapInterface;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\ResourceModel\Document;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\App\Progress;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(CyclomaticComplexity)
 */
class Data implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progressBar;

    /**
     * Progress instance, saves the state of the process
     *
     * @var Progress
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var bool
     */
    protected $copyDirectly;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param Progress $progress
     * @param Logger $logger
     * @param Config $config
     * @param Helper $helper
     *
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        Progress $progress,
        Logger $logger,
        Config $config,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('map_file');
        $this->progressBar = $progressBar;
        $this->progress = $progress;
        $this->logger = $logger;
        $this->config = $config;
        $this->copyDirectly = (bool)$this->config->getOption('direct_document_copy');
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progressBar->start(count($this->source->getDocumentList()), LogManager::LOG_LEVEL_INFO);
        $sourceDocuments = $this->source->getDocumentList();
        $stage = 'run';
        $processedDocuments = $this->progress->getProcessedEntities($this, $stage);
        foreach (array_diff($sourceDocuments, $processedDocuments) as $sourceDocName) {
            $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            if (!$destDocument) {
                continue;
            }
            $this->destination->clearDocument($destinationName);
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
            $doCopy = $recordTransformer === null && $this->copyDirectly;
            if ($doCopy && $this->isCopiedDirectly($sourceDocument, $destDocument)) {
                $this->progressBar->start(1, LogManager::LOG_LEVEL_DEBUG);
            } else {
                $pageNumber = 0;
                $this->progressBar->start(
                    ceil($this->source->getRecordsCount($sourceDocName) / $this->source->getPageSize($sourceDocName)),
                    LogManager::LOG_LEVEL_DEBUG
                );
                while (!empty($items = $this->source->getRecords($sourceDocName, $pageNumber))) {
                    $pageNumber++;
                    $destinationRecords = $destDocument->getRecords();
                    foreach ($items as $data) {
                        if ($recordTransformer) {
                            /** @var Record $record */
                            $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
                            /** @var Record $destRecord */
                            $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                            $recordTransformer->transform($record, $destRecord);
                        } else {
                            $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
                        }
                        $destinationRecords->addRecord($destRecord);
                    }
                    $this->source->setLastLoadedRecord($sourceDocName, end($items));
                    $this->progressBar->advance(LogManager::LOG_LEVEL_DEBUG);
                    $fieldsUpdateOnDuplicate = $this->helper->getFieldsUpdateOnDuplicate($destinationName);
                    $this->destination->saveRecords($destinationName, $destinationRecords, $fieldsUpdateOnDuplicate);
                }
            }
            $this->source->setLastLoadedRecord($sourceDocName, []);
            $this->progress->addProcessedEntity($this, $stage, $sourceDocName);
            $this->progressBar->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Get record transformer
     *
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return \Migration\RecordTransformer
     */
    public function getRecordTransformer(Document $sourceDocument, Document $destDocument)
    {
        if ($this->canJustCopy($sourceDocument, $destDocument)) {
            return null;
        }
        /** @var \Migration\RecordTransformer $recordTransformer */
        $recordTransformer = $this->recordTransformerFactory->create(
            [
                'sourceDocument' => $sourceDocument,
                'destDocument' => $destDocument,
                'mapReader' => $this->map
            ]
        );
        $recordTransformer->init();
        return $recordTransformer;
    }

    /**
     * Can just copy
     *
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return bool
     */
    public function canJustCopy(Document $sourceDocument, Document $destDocument)
    {
        return $this->haveEqualStructure($sourceDocument, $destDocument)
            && !$this->hasHandlers($sourceDocument, MapInterface::TYPE_SOURCE)
            && !$this->hasHandlers($destDocument, MapInterface::TYPE_DEST);
    }

    /**
     * Have equal structure
     *
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return string bool
     */
    protected function haveEqualStructure(Document $sourceDocument, Document $destDocument)
    {
        $diff = array_diff_key(
            $sourceDocument->getStructure()->getFields(),
            $destDocument->getStructure()->getFields()
        );
        return empty($diff);
    }

    /**
     * Is copied directly
     *
     * @param Document $sourceDocument
     * @param Document $destinationDocument
     * @return bool
     */
    protected function isCopiedDirectly(Document $sourceDocument, Document $destinationDocument)
    {
        if (!$this->copyDirectly) {
            return;
        }
        $result = true;
        $schema = $this->config->getSource()['database']['name'];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->source->getAdapter()->getSelect();
        $select->from($this->source->addDocumentPrefix($sourceDocument->getName()), '*', $schema);
        try {
            $this->destination->getAdapter()->insertFromSelect(
                $select,
                $this->destination->addDocumentPrefix($destinationDocument->getName()),
                array_keys($sourceDocument->getStructure()->getFields())
            );
        } catch (\Exception $e) {
            $this->copyDirectly = false;
            $this->logger->warning(
                'Document ' . $sourceDocument->getName() . ' can not be copied directly because of error: '
                . $e->getMessage()
            );
            $result = false;
        }

        return $result;
    }

    /**
     * Has handlers
     *
     * @param Document $document
     * @param string $type
     * @return bool
     */
    protected function hasHandlers(Document $document, $type)
    {
        $result = false;
        foreach (array_keys($document->getStructure()->getFields()) as $fieldName) {
            $handlerConfig = $this->map->getHandlerConfigs($document->getName(), $fieldName, $type);
            if (!empty($handlerConfig)) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}
