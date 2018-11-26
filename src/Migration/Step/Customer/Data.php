<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\App\Step\StageInterface;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\App\Progress;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\Step\Customer\Model;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Migration\Step\DatabaseStage implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ResourceModel\RecordFactory
     */
    private $recordFactory;

    /**
     * @var Map
     */
    private $map;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    private $recordTransformerFactory;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * Progress instance, saves the state of the process
     *
     * @var Progress
     */
    private $progress;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var Model\AttributesDataToCustomerEntityRecords
     */
    private $attributesDataToCustomerEntityRecords;

    /**
     * @var Model\AttributesDataToSkip
     */
    private $attributesDataToSkip;

    /**
     * @var Model\AttributesToStatic
     */
    private $attributesToStatic;

    /**
     * @param \Migration\Config $config
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param Progress $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Model\AttributesDataToCustomerEntityRecords $attributesDataToCustomerEntityRecords
     * @param Model\AttributesDataToSkip $attributesDataToSkip
     * @param Model\AttributesToStatic $attributesToStatic
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Migration\Config $config,
        ProgressBar\LogLevelProcessor $progressBar,
        Progress $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Model\AttributesDataToCustomerEntityRecords $attributesDataToCustomerEntityRecords,
        Model\AttributesDataToSkip $attributesDataToSkip,
        Model\AttributesToStatic $attributesToStatic,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('customer_map_file');
        $this->progressBar = $progressBar;
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->logger = $logger;
        $this->attributesDataToCustomerEntityRecords = $attributesDataToCustomerEntityRecords;
        $this->attributesDataToSkip = $attributesDataToSkip;
        $this->attributesToStatic = $attributesToStatic;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $stage = 'run';
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        $sourceEntityDocuments = array_keys($this->readerGroups->getGroup('source_entity_documents'));
        $sourceDataDocuments = array_diff($sourceDocuments, $sourceEntityDocuments);
        $skippedAttributes = array_keys($this->attributesDataToSkip->getSkippedAttributes());
        $processedDocuments = $this->progress->getProcessedEntities($this, $stage);
        $this->progressBar->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);
        foreach (array_diff($sourceEntityDocuments, $processedDocuments) as $sourceEntityDocument) {
            $this->transformDocumentRecords($sourceEntityDocument);
            $this->progress->addProcessedEntity($this, $stage, $sourceEntityDocument);
        }
        foreach (array_diff($sourceDataDocuments, $processedDocuments) as $sourceDataDocument) {
            $this->transformDocumentRecords($sourceDataDocument, $skippedAttributes);
            $this->progress->addProcessedEntity($this, $stage, $sourceDataDocument);
        }
        $this->attributesToStatic->update();
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Migrate given document to the destination with possibility of excluding some of the records
     *
     * @param mixed $sourceDocName
     * @param array|null $attributesToSkip
     */
    private function transformDocumentRecords(
        $sourceDocName,
        array $attributesToSkip = null
    ) {
        $sourceEntityDocuments = array_keys($this->readerGroups->getGroup('source_entity_documents'));
        $sourceDocument = $this->source->getDocument($sourceDocName);
        $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
        if (!$destinationName) {
            return;
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
        $pageNumber = 0;
        $this->logger->debug('migrating', ['table' => $sourceDocName]);
        $this->progressBar->start(
            ceil($this->source->getRecordsCount($sourceDocName) / $this->source->getPageSize($sourceDocName)),
            LogManager::LOG_LEVEL_DEBUG
        );
        while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
            $pageNumber++;
            $destinationRecords = $destDocument->getRecords();
            foreach ($bulk as $recordData) {
                if ($attributesToSkip !== null
                    && isset($recordData['attribute_id'])
                    && in_array($recordData['attribute_id'], $attributesToSkip)
                ) {
                    continue;
                }
                /** @var Record $record */
                $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                /** @var Record $destRecord */
                $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                $recordTransformer->transform($record, $destRecord);
                $destinationRecords->addRecord($destRecord);
            }
            if (in_array($sourceDocName, $sourceEntityDocuments)) {
                $this->attributesDataToCustomerEntityRecords
                    ->updateCustomerEntities($sourceDocName, $destinationRecords);
            }
            $this->source->setLastLoadedRecord($sourceDocName, end($bulk));
            $this->progressBar->advance(LogManager::LOG_LEVEL_DEBUG);
            $this->destination->saveRecords($destinationName, $destinationRecords);
        }
        $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
        $this->progressBar->finish(LogManager::LOG_LEVEL_DEBUG);
    }
}
