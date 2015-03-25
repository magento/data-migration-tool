<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Handler;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderMain;
use Migration\Resource;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\ProgressBar;

/**
 * Class Migrate
 */
class Migrate
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
     * @var MapReaderMain
     */
    protected $mapReader;

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
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapReaderMain $mapReader
     */
    public function __construct(
        ProgressBar $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapReaderMain $mapReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start(count($this->source->getDocumentList()));
        $sourceDocuments = $this->source->getDocumentList();
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->mapReader->getDocumentMap($sourceDocName, MapReaderInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);

            $recordTranformer = $this->getRecordTransformer($sourceDocument, $destDocument);
            $pageNumber = 0;
            while (!empty($items = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($items as $data) {
                    /** @var Record $record */
                    /** @var Record $destRecord */
                    if ($recordTranformer) {
                        $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
                        $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                        $recordTranformer->transform($record, $destRecord);
                    } else {
                        $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
                    }
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
        }
        $this->progress->finish();
        return true;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return \Migration\RecordTransformer
     */
    public function getRecordTransformer(Document $sourceDocument, Document $destDocument)
    {
        if (!$this->canJustCopy($sourceDocument, $destDocument)) {
            return null;
        }
        /** @var \Migration\RecordTransformer $recordTranformer */
        $recordTranformer = $this->recordTransformerFactory->create(
            [
                'sourceDocument' => $sourceDocument,
                'destDocument' => $destDocument,
                'mapReader' => $this->mapReader
            ]
        );
        $recordTranformer->init();
        return $recordTranformer;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return bool
     */
    public function canJustCopy(Document $sourceDocument, Document $destDocument)
    {
        return $this->haveEqualStructure($sourceDocument, $destDocument)
            && !$this->hasHandlers($sourceDocument, MapReaderInterface::TYPE_SOURCE)
            && !$this->hasHandlers($destDocument, MapReaderInterface::TYPE_DEST);
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return string bool
     */
    protected function haveEqualStructure(Document $sourceDocument, Document $destDocument)
    {
        $diff = array_diff_key(
            $sourceDocument->getStructure()->getFields(),
            $destDocument->getStructure()->getFields()
        );
        return empty($diff);
    }

    /**
     * @param Document $document
     * @param string $type
     * @return bool
     */
    protected function hasHandlers(Document $document, $type)
    {
        $result = false;
        foreach (array_keys($document->getStructure()->getFields()) as $fieldName) {
            if ($this->mapReader->getHandlerConfig($document->getName(), $fieldName, $type)) {
                $result = true;
                break;
            }
        }
        return $result;
    }
}
