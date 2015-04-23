<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\RollbackInterface;
use Migration\Reader\MapInterface;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Resource;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\App\ProgressBar;
use Migration\App\Progress;

/**
 * Class Data
 */
class Data implements RollbackInterface
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
    protected $progressBar;

    /**
     * Progress instance, saves the state of the process
     *
     * @var Progress
     */
    protected $progress;

    /**
     * @param ProgressBar $progressBar
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param Progress $progress
     */
    public function __construct(
        ProgressBar $progressBar,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        Progress $progress
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('map_file');
        $this->progressBar = $progressBar;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progressBar->start(count($this->source->getDocumentList()));
        $sourceDocuments = $this->source->getDocumentList();
        // TODO: during steps refactoring MAGETWO-35749 stage will be removed
        $stage = 'run';
        $processedDocuments = $this->progress->getProcessedEntities($this, $stage);
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progressBar->advance();
            if (in_array($sourceDocName, $processedDocuments)) {
                continue;
            }
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);

            $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
            $pageNumber = 0;
            while (!empty($items = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($items as $data) {
                    if ($recordTransformer) {
                        /** @var Record $record */
                        $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
                        /** @var Record $destRecord */
                        $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                        $recordTransformer->transform($record, $destRecord);
                    } else {
                        $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
                    }
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
            $this->progress->addProcessedEntity($this, $stage, $sourceDocName);
        }
        $this->progressBar->finish();
        return true;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return \Migration\RecordTransformer
     */
    public function getRecordTransformer(Document $sourceDocument, Document $destDocument)
    {
        if ($this->canJustCopy($sourceDocument, $destDocument)) {
            return null;
        }
        /** @var \Migration\RecordTransformer $recordTransformer */
        $recordTransformer = $this->recordTransformerFactory->create(
            [
                'sourceDocument' => $sourceDocument,
                'destDocument' => $destDocument,
                'mapReader' => $this->map
            ]
        );
        $recordTransformer->init();
        return $recordTransformer;
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destDocument
     * @return bool
     */
    public function canJustCopy(Document $sourceDocument, Document $destDocument)
    {
        return $this->haveEqualStructure($sourceDocument, $destDocument)
            && !$this->hasHandlers($sourceDocument, MapInterface::TYPE_SOURCE)
            && !$this->hasHandlers($destDocument, MapInterface::TYPE_DEST);
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
            $handlerConfig = $this->map->getHandlerConfig($document->getName(), $fieldName, $type);
            if (!empty($handlerConfig)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        return true;
    }
}
