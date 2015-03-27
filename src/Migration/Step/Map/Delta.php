<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\MapReader\MapReaderChangelog;
use Migration\MapReaderInterface;
use Migration\Resource\Source;
use Migration\Resource;
use Migration\ProgressBar;

class Delta
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapReaderChangelog
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var Migrate
     */
    protected $migrate;

    /**
     * @param Source $source
     * @param MapReaderChangelog $mapReader
     * @param ProgressBar $progress
     * @param Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     */
    public function __construct(
        Source $source,
        MapReaderChangelog $mapReader,
        ProgressBar $progress,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Migrate $migrate
    ) {
        $this->source = $source;
        $this->mapReader = $mapReader;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->progress = $progress;
        $this->migrate = $migrate;
    }

    /**
     * @return bool
     */
    public function setUpChangeLog()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        $this->progress->start(count($deltaDocuments));
        foreach ($deltaDocuments as $documentName => $idKey) {
            $this->progress->advance();
            $this->source->createDelta($documentName, $idKey);
        }
        $this->progress->finish();
        return true;
    }

    /**
     * @return bool
     */
    public function delta()
    {
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        foreach ($deltaDocuments as  $documentName => $idKey) {

            $items = $this->source->getChangedRecords($documentName, $idKey);
            if (!empty($items)) {
                echo "\n $documentName has changes";
            } else {
                continue;
            }
            $sourceDocument = $this->source->getDocument($documentName);

            $destinationName = $this->mapReader->getDocumentMap($documentName, MapReaderInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);

            $recordTransformer = $this->migrate->getRecordTransformer($sourceDocument, $destDocument);
            do {
                $destinationRecords = $destDocument->getRecords();
                $ids = [];
                foreach ($items as $data) {
                    /** @var Resource\Record $record */
                    /** @var Resource\Record $destRecord */
                    if ($recordTransformer) {
                        $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
                        $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                        $recordTransformer->transform($record, $destRecord);
                    } else {
                        $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
                    }
                    $ids[] = $data[$idKey];
                    $destinationRecords->addRecord($destRecord);
                    echo '.';
                }
                $this->destination->updateChangedRecords($destinationName, $destinationRecords);
                $this->source->deleteRecords($this->source->getChangeLogName($documentName), $idKey, $ids);
            } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
        }
        return true;
    }
}
