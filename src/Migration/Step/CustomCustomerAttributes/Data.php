<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\Config;
use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\App\ProgressBar;
use Migration\Resource\Record;
use Migration\Resource\RecordFactory;
use Migration\Step\CustomCustomerAttributes;
use Migration\Logger\Manager as LogManager;

/**
 * Class Data
 */
class Data extends CustomCustomerAttributes implements \Migration\App\Step\RollbackInterface
{
    /**
     * @var RecordFactory
     */
    protected $factory;

    /**
     * @param Config $config
     * @param Source $source
     * @param Destination $destination
     * @param ProgressBar $progress
     * @param RecordFactory $factory
     * @throws \Migration\Exception
     */
    public function __construct(
        Config $config,
        Source $source,
        Destination $destination,
        ProgressBar $progress,
        RecordFactory $factory
    ) {
        parent::__construct($config, $source, $destination, $progress);
        $this->factory = $factory;
    }

    /**
     * Run step
     *
     * @return bool
     */
    public function perform()
    {
        /** @var \Migration\Resource\Adapter\Mysql $sourceAdapter */
        $sourceAdapter = $this->source->getAdapter();
        /** @var \Migration\Resource\Adapter\Mysql $destinationAdapter */
        $destinationAdapter = $this->destination->getAdapter();

        if (LogManager::getLogLevel() != LogManager::LOG_LEVEL_DEBUG) {
            $this->progress->start(count($this->getDocumentList()));
        }
        foreach ($this->getDocumentList() as $sourceDocumentName => $destinationDocumentName) {
            if (LogManager::getLogLevel() != LogManager::LOG_LEVEL_DEBUG) {
                $this->progress->advance();
            }

            $sourceTable =  $sourceAdapter->getTableDdlCopy(
                $this->source->addDocumentPrefix($sourceDocumentName),
                $this->destination->addDocumentPrefix($destinationDocumentName)
            );
            $destinationTable = $destinationAdapter->getTableDdlCopy(
                $this->destination->addDocumentPrefix($destinationDocumentName),
                $this->destination->addDocumentPrefix($destinationDocumentName)
            );
            foreach ($sourceTable->getColumns() as $columnData) {
                $destinationTable->setColumn($columnData);
            }
            $destinationAdapter->createTableByDdl($destinationTable);

            $destinationDocument = $this->destination->getDocument($destinationDocumentName);
            $pageNumber = 0;
            if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_DEBUG) {
                $this->progress->start($this->source->getRecordsCount($sourceDocumentName));
            }
            while (!empty($sourceRecords = $this->source->getRecords($sourceDocumentName, $pageNumber))) {
                $pageNumber++;
                $recordsToSave = $destinationDocument->getRecords();
                foreach ($sourceRecords as $recordData) {
                    if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_DEBUG) {
                        $this->progress->advance();
                    }
                    /** @var Record $destinationRecord */
                    $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
                    $destinationRecord->setData($recordData);
                    $recordsToSave->addRecord($destinationRecord);
                }
                $this->destination->saveRecords($destinationDocument->getName(), $recordsToSave);
            };
            if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_DEBUG) {
                $this->progress->finish();
            }
        }
        if (LogManager::getLogLevel() != LogManager::LOG_LEVEL_DEBUG) {
            $this->progress->finish();
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        return true;
    }
}
