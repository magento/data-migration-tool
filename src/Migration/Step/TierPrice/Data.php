<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\App\Step\StageInterface;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        Logger $logger,
        Helper $helper,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->progress = $progress;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('tier_price_map_file');
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        foreach ($this->helper->getDestinationDocuments() as $documentName) {
            $this->destination->clearDocument($documentName);
        }
        $sourceDocuments = $this->helper->getSourceDocuments();
        $this->progress->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);

        foreach ($sourceDocuments as $sourceDocName) {
            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocName), LogManager::LOG_LEVEL_DEBUG);

            while (!empty($items = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $this->progress->advance(LogManager::LOG_LEVEL_INFO);

                $pageNumber++;
                $destinationName = $this->helper->getMappedDocumentName($sourceDocName, MapInterface::TYPE_SOURCE);
                $destDocument = $this->destination->getDocument($destinationName);
                $destinationRecords = $destDocument->getRecords();

                $sourceDocument = $this->source->getDocument($sourceDocName);
                /** @var \Migration\RecordTransformer $recordTransformer */
                $recordTransformer = $this->recordTransformerFactory->create(
                    [
                        'sourceDocument' => $sourceDocument,
                        'destDocument' => $destDocument,
                        'mapReader' => $this->map
                    ]
                );
                $recordTransformer->init();

                foreach ($items as $recordData) {
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);

                    $recordData['value_id'] = null;
                    /** @var Record $sourceRecord */
                    $sourceRecord = $this->recordFactory->create(
                        ['document' => $sourceDocument, 'data' => $recordData]
                    );
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($sourceRecord, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords, true);
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }

        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }
}
