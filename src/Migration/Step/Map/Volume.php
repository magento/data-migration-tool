<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderMain;
use Migration\Resource;
use Migration\App\ProgressBar;

class Volume implements StageInterface
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
     * @var MapReaderMain
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
    protected $progressBar;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderMain $mapReader
     * @param ProgressBar $progressBar
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderMain $mapReader,
        ProgressBar $progressBar
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->logger = $logger;
        $this->progressBar = $progressBar;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = $this->source->getDocumentList();
        $this->progressBar->start(count($sourceDocuments));
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progressBar->advance();
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
        $this->progressBar->finish();
        $this->printErrors();
        return $isSuccess;
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
