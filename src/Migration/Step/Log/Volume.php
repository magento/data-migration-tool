<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\Resource;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;

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
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerList;

    /**
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapFactory $mapFactory
     * @param ProgressBar\LogLevelProcessor $progress
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        ProgressBar\LogLevelProcessor $progress,
        GroupsFactory $groupsFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('log_map_file');
        $this->logger = $logger;
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('log_document_groups_file');
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $isSuccess = true;
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
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
        if (!$this->checkCleared(array_keys($this->readerGroups->getGroup('destination_documents_to_clear')))) {
            $isSuccess = false;
            $this->errors[] = 'Destination log documents are not cleared';
        }
        $this->progress->finish();
        $this->logErrors();
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
        return count($this->readerGroups->getGroup('destination_documents_to_clear'))
            + count($this->readerGroups->getGroup('source_documents'));
    }

    /**
     * Log Volume check errors
     * @return void
     */
    protected function logErrors()
    {
        foreach ($this->errors as $error) {
            $this->logger->error($error);
        }
    }
}
