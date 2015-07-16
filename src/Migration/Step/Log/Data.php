<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerList;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('log_map_file');
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('log_document_groups_file');
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        foreach ($sourceDocuments as $sourceDocName) {
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
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
                    'mapReader' => $this->map
                ]
            );
            $recordTransformer->init();
            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocName), LogManager::LOG_LEVEL_DEBUG);
            while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($bulk as $recordData) {
                    $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->clearLog(array_keys($this->readerGroups->getGroup('destination_documents_to_clear')));
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
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
        $iterations = 0;
        foreach (array_keys($this->readerGroups->getGroup('source_documents')) as $document) {
            $iterations += $this->source->getRecordsCount($document);
        }

        return count($this->readerGroups->getGroup('destination_documents_to_clear'))
            + $iterations;
    }
}
