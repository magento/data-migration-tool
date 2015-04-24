<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderLog;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;

/**
 * Class Data
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
     * @var MapReaderLog
     */
    protected $mapReader;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * ProgressBar instance
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param ProgressBar $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapReaderLog $mapReader
     */
    public function __construct(
        ProgressBar $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapReaderLog $mapReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        if (LogManager::getLogLevel() != LogManager::LOG_LEVEL_DEBUG) {
            $this->progress->start($this->getIterationsCount());
        }
        $sourceDocuments = array_keys($this->mapReader->getDocumentList());
        foreach ($sourceDocuments as $sourceDocName) {
            if (LogManager::getLogLevel() != LogManager::LOG_LEVEL_DEBUG) {
                $this->progress->advance();
            }
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->mapReader->getDocumentMap($sourceDocName, MapReaderInterface::TYPE_SOURCE);
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
                    'mapReader' => $this->mapReader
                ]
            );
            $recordTransformer->init();
            $pageNumber = 0;
            if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_DEBUG) {
                $this->progress->start($this->source->getRecordsCount($sourceDocName));
            }
            while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($bulk as $recordData) {
                    if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_DEBUG) {
                        $this->progress->advance();
                    }
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
            if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_DEBUG) {
                $this->progress->finish();
            }
        }
        $this->clearLog($this->mapReader->getDestDocumentsToClear());
        if (LogManager::getLogLevel() != LogManager::LOG_LEVEL_DEBUG) {
            $this->progress->finish();
        }
        return true;
    }

    /**
     * @param array $documents
     * @return void
     */
    protected function clearLog($documents)
    {
        foreach ($documents as $documentName) {
            $this->progress->advance();
            $this->destination->clearDocument($documentName);
        }
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->mapReader->getDestDocumentsToClear()) + count($this->mapReader->getDocumentList());
    }
}
