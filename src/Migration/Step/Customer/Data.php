<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Migration\Step\DatabaseStage implements StageInterface
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
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerList;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @param \Migration\Config $config
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Helper $helper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Migration\Config $config,
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('customer_map_file');
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->logger = $logger;
        $this->helper = $helper;
        parent::__construct($config);
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        foreach ($sourceDocuments as $sourceDocName) {
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);

            /** @var \Migration\RecordTransformer $recordTransformer */
            $recordTransformer = $this->recordTransformerFactory->create(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destDocument,
                    'mapReader' => $this->map
                ]
            );
            $recordTransformer->init();

            $attributeType = $this->helper->getAttributeType($sourceDocName);

            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $this->progress->start(
                ceil($this->source->getRecordsCount($sourceDocName) / $this->source->getPageSize($sourceDocName)),
                LogManager::LOG_LEVEL_DEBUG
            );
            while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;

                $destinationRecords = $destDocument->getRecords();
                foreach ($bulk as $recordData) {
                    $this->source->setLastLoadedRecord($sourceDocName, $recordData);
                    if ($this->helper->isSkipRecord($attributeType, $sourceDocName, $recordData)) {
                        continue;
                    }
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);

                $this->helper->updateAttributeData($attributeType, $sourceDocName, $destinationRecords);

                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->helper->updateEavAttributes();
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        $iterations = 0;
        foreach (array_keys($this->readerGroups->getGroup('source_documents')) as $document) {
            $iterations += $this->source->getRecordsCount($document);
        }

        return $iterations;
    }
}
