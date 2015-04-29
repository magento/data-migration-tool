<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Resource;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;

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
     * @var Map
     */
    protected $map;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * LogLevelProcessor instance
     *
     * @var ProgressBar\LogLevelProcessor
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
     * @param MapFactory $mapFactory
     * @param ProgressBar\LogLevelProcessor $progressBar
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        ProgressBar\LogLevelProcessor $progressBar
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('map_file');
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
        $this->progressBar->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
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
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
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
