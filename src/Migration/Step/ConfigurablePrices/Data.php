<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\Config;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var Mysql
     */
    protected $sourceAdapter;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param Config $config
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        Logger $logger,
        Helper $helper,
        Config $config
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->destination = $destination;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->helper->init();
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $document = $this->helper->getDocumentList();
        $sourceDocumentName = $document['source'];
        $destinationDocumentName = $document['destination'];
        $destinationDocument = $this->destination->getDocument($destinationDocumentName);
        $pageNumber = 0;
        $this->logger->debug('migrating', ['table' => $sourceDocumentName]);
        $this->progress->start($this->source->getRecordsCount($sourceDocumentName), LogManager::LOG_LEVEL_DEBUG);
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->helper->getConfigurablePrice();
        while (!empty($bulk = $this->getRecords($sourceDocumentName, $select, $pageNumber))) {
            $pageNumber++;
            $destinationCollection = $destinationDocument->getRecords();
            foreach ($bulk as $recordData) {
                $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                /** @var Record $destinationRecord */
                $destinationRecord = $this->recordFactory->create(
                    ['document' => $destinationDocument, 'data' => $recordData]
                );
                $destinationCollection->addRecord($destinationRecord);
            }
            $this->destination->saveRecords($destinationDocumentName, $destinationCollection, true);
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Get iterations count
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        $iterations = 0;
        $document = $this->helper->getDocumentList();
        $iterations += $this->source->getRecordsCount($document['source']);
        return $iterations;
    }

    /**
     * Get records
     *
     * @param string $sourceDocumentName
     * @param \Magento\Framework\DB\Select $select
     * @param int $pageNumber
     * @return array
     */
    protected function getRecords($sourceDocumentName, \Magento\Framework\DB\Select $select, $pageNumber)
    {
        $select->limit(
            $this->source->getPageSize($sourceDocumentName),
            $pageNumber * $this->source->getPageSize($sourceDocumentName)
        );
        return $this->sourceAdapter->loadDataFromSelect($select);
    }
}
