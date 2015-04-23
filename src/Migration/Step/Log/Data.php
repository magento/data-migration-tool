<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Reader\MapInterface;
use Migration\Reader\ListsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\App\ProgressBar;

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
     * @var Map
     */
    protected $map;

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
     * @var \Migration\Reader\Lists
     */
    protected $readerList;

    /**
     * @param ProgressBar $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param ListsFactory $listsFactory
     */
    public function __construct(
        ProgressBar $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        ListsFactory $listsFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('log_map_file');
        $this->progress = $progress;
        $this->readerList = $listsFactory->create('log_list_file');
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $sourceDocuments = $this->readerList->getList('source_documents');
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
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
            while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($bulk as $recordData) {
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
        }
        $this->clearLog($this->readerList->getList('destination_documents_to_clear'));
        $this->progress->finish();
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
        return count($this->readerList->getList('destination_documents_to_clear'))
            + count($this->readerList->getList('source_documents'));
    }
}
