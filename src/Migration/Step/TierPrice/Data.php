<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Resource;
use Migration\Resource\Record;
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
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

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
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Logger $logger,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->progress = $progress;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        // catalog_product_entity_tier_price should be migrated first to save same value_id as into magento1
        $sourceDocuments = array_keys($this->helper->getSourceDocumentFields());
        $this->progress->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);

        $destinationName = $this->helper->getDestinationName();
        $this->destination->clearDocument($destinationName);
        $destDocument = $this->destination->getDocument($destinationName);

        foreach ($sourceDocuments as $sourceDocName) {

            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocName), LogManager::LOG_LEVEL_DEBUG);

            while (!empty($items = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($items as $recordData) {
                    unset($recordData['value_id']);
                    $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create([
                        'document'  => $destDocument,
                        'data'      => $recordData,
                    ]);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }

        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }
}
