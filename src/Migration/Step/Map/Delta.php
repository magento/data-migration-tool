<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\MapReader\MapReaderChangelog;
use Migration\MapReader\MapReaderMain;
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
    protected $mapReaderChangelog;

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
     * @param MapReaderChangelog $mapReaderChangelog
     * @param MapReaderMain $mapReader
     * @param ProgressBar $progress
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Migrate $migrate
     */
    public function __construct(
        Source $source,
        MapReaderChangelog $mapReaderChangelog,
        MapReaderMain $mapReader,
        ProgressBar $progress,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Migrate $migrate
    ) {
        $this->source = $source;
        $this->mapReaderChangelog = $mapReaderChangelog;
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
        $deltaDocuments = $this->mapReaderChangelog->getDeltaDocuments($this->source->getDocumentList());
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
        $deltaDocuments = $this->mapReaderChangelog->getDeltaDocuments($this->source->getDocumentList());
        foreach ($deltaDocuments as  $documentName => $idKey) {

            $items = $this->source->getChangedRecords($documentName, $idKey);
            if (empty($items)) {
                continue;
            }
            $sourceDocument = $this->source->getDocument($documentName);

            $destinationName = $this->mapReader->getDocumentMap($documentName, MapReaderInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }

            echo "\n $documentName have changes \n";

            $destDocument = $this->destination->getDocument($destinationName);

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
