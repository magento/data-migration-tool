<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Handler;
use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\ProgressBar;

class Run
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
     * @var MapReader
     */
    protected $mapReader;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

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
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapReader $mapReader
     * @throws \Exception
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapReader $mapReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->mapReader = $mapReader;
        $this->mapReader->init();
        $this->logger =  $logger;
        $this->progress = $progress;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $sourceDocuments = $this->source->getDocumentList();
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->mapReader->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);

            /** @var \Migration\RecordTransformer $recordTranformer */
            $recordTranformer = $this->recordTransformerFactory->create(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destDocument,
                    'mapReader' => $this->mapReader
                ]
            );
            $recordTranformer->init();

            $pageNumber = 0;
            while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($bulk as $recordData) {
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTranformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
        }
        $this->progress->finish();
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        $sourceDocuments = $this->source->getDocumentList();
        $destDocuments = $this->destination->getDocumentList();
        return count($sourceDocuments) + count($destDocuments);
    }


    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return get_class($this);
    }
}
