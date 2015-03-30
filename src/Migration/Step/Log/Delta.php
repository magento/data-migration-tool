<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Log;

use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderLog;
use Migration\ProgressBar;

class Delta
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var MapReaderLog
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var \Migration\Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @param Source $source
     * @param Destination $destination
     * @param MapReaderLog $mapReader
     * @param ProgressBar $progress
     * @param \Migration\Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     */
    public function __construct(
        Source $source,
        Destination $destination,
        MapReaderLog $mapReader,
        ProgressBar $progress,
        \Migration\Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
    }

    /**
     * @return bool
     */
    public function delta()
    {
        $this->progress->start(count($this->mapReader->getDocumentList()));
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        foreach ($deltaDocuments as $deltaDocName => $idField) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($deltaDocName);
            $destinationName = $this->mapReader->getDocumentMap($deltaDocName, MapReaderInterface::TYPE_SOURCE);
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
                    'mapReader' => $this->mapReader
                ]
            );
            $recordTransformer->init();

            $pageNumber = 0;
            while (!empty($sourceRecords = $this->source->getChangedRecords($sourceDocument, $idField))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($sourceRecords as $recordData) {
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->updateChangedRecords($destinationName, $destinationRecords);
            }
            break;
        }
        $this->progress->finish();
        return true;
    }
}
