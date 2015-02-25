<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Volume;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;
use Migration\ProgressBar;

class Map
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
     * @var MapReader
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
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     * @param \Migration\Config $config
     * @param ProgressBar $progress
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader,
        \Migration\Config $config,
        ProgressBar $progress
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->mapReader->init($config->getOption('map_file'));
        $this->logger = $logger;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = $this->source->getDocumentList();
        $this->progress->start(count($sourceDocuments));
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $destinationName = $this->mapReader->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $sourceCount = $this->source->getRecordsCount($sourceDocName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $isSuccess = false;
                $this->logger->error(sprintf(
                    PHP_EOL . 'Volume check failed for the destination document %s',
                    PHP_EOL . $destinationName
                ));
            }
        }
        $this->progress->finish();
        return $isSuccess;
    }
}
