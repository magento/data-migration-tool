<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\ListsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\Resource;
use Migration\ProgressBar;

/**
 * Class Volume
 */
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
     * @var \Migration\Reader\Lists
     */
    protected $readerList;

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapFactory $mapFactory
     * @param ProgressBar $progress
     * @param ListsFactory $listsFactory
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        ProgressBar $progress,
        ListsFactory $listsFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('log_map_file');
        $this->logger = $logger;
        $this->progress = $progress;
        $this->readerList = $listsFactory->create('log_list_file');
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = $this->readerList->getList('source_documents');
        $this->progress->start($this->getIterationsCount());
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
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
        if (!$this->checkCleared($this->readerList->getList('destination_documents_to_clear'))) {
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
        return count($this->readerList->getList('destination_documents_to_clear'))
            + count($this->readerList->getList('source_documents'));
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
