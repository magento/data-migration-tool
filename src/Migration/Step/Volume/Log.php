<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Volume;

use Migration\Logger\Logger;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderLog;
use Migration\Resource;
use Migration\ProgressBar;

/**
 * Class Log
 */
class Log
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
     * @var MapReaderLog
     */
    protected $mapReader;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ProgressBar instance
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderLog $mapReader
     * @param ProgressBar $progress
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderLog $mapReader,
        ProgressBar $progress
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->logger = $logger;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = array_keys($this->mapReader->getDocumentList());
        $this->progress->start($this->getIterationsCount());
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $destinationName = $this->mapReader->getDocumentMap($sourceDocName, MapReaderInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $sourceCount = $this->source->getRecordsCount($sourceDocName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $isSuccess = false;
                $this->errors[] = 'Volume check failed for the destination document: ' . $destinationName;
            }
        }
        if (!$this->checkCleared($this->mapReader->getDestDocumentsToClear())) {
            $isSuccess = false;
            $this->errors[] = 'Destination log documents are not cleared';
        }
        $this->progress->finish();
        $this->printErrors();
        return $isSuccess;
    }

    /**
     * @param array $documents
     * @return bool
     */
    protected function checkCleared($documents)
    {
        $documentsAreEmpty = true;
        foreach ($documents as $documentName) {
            $this->progress->advance();
            $destinationCount = $this->destination->getRecordsCount($documentName);
            if ($destinationCount > 0) {
                $documentsAreEmpty = false;
                break;
            }
            $destinationCount = null;
        }
        return $documentsAreEmpty;
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

    /**
     * Print Volume check errors
     * @return void
     */
    protected function printErrors()
    {
        foreach ($this->errors as $error) {
            $this->logger->error(PHP_EOL . $error);
        }
    }
}
