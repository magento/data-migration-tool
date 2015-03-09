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
use Migration\Step\Log\Helper;

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
     * @var Helper
     */
    protected $helper;

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     * @param \Migration\Config $config
     * @param ProgressBar $progress
     * @param Helper $helper
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader,
        \Migration\Config $config,
        ProgressBar $progress,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->mapReader->init($config->getOption('map_file'));
        $this->logger = $logger;
        $this->progress = $progress;
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = array_keys($this->helper->getDocumentList());
        $this->progress->start($this->getIterationsCount());
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
        if (!$this->checkCleared($this->helper->getDestDocumentsToClear())) {
            $isSuccess = false;
            $this->logger->error(sprintf(PHP_EOL . 'Destination log documents are not cleared'));
        }
        $this->progress->finish();
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
        return count($this->helper->getDestDocumentsToClear()) + count($this->helper->getDocumentList());
    }
}
