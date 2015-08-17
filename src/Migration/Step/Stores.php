<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\StageInterface;
use Migration\Resource;
use Migration\RecordTransformerFactory;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;

/**
 * Class Integrity
 */
class Stores implements StageInterface
{
    /**
     * @var array
     */
    protected $documents;

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
     * @var RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param RecordTransformerFactory $recordTransformerFactory
     * @param Resource\RecordFactory $recordFactory
     * @param string $stage
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        RecordTransformerFactory $recordTransformerFactory,
        Resource\RecordFactory $recordFactory,
        $stage
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->source = $source;
        $this->destination = $destination;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->recordFactory = $recordFactory;
        $this->documents = [];
        $this->stage = $stage;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        if (!method_exists($this, $this->stage)) {
            throw new \Migration\Exception('Invalid step configuration');
        }

        return call_user_func([$this, $this->stage]);
    }

    /**
     * Integrity check
     *
     * @return bool
     */
    protected function integrity()
    {
        $result = true;
        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            $result &= (bool)$this->source->getDocument($sourceName);
            $result &= (bool)$this->destination->getDocument($destinationName);
        }
        $this->progress->finish();
        return (bool)$result;
    }

    /**
     * @return bool
     */
    protected function data()
    {
        $this->progress->start(count($this->getDocumentList()));
        $documents = $this->getDocumentList();
        foreach ($documents as $sourceDocName => $destDocName) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationDocument = $this->destination->getDocument($destDocName);
            $this->destination->clearDocument($destDocName);
            $pageNumber = 0;
            while (!empty($sourceRecords = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $recordsToSave = $destinationDocument->getRecords();
                foreach ($sourceRecords as $recordData) {
                    /** @var Resource\Record $destinationRecord */
                    $destinationRecord = $this->recordFactory->create(['document' => $destinationDocument]);
                    if ($this->haveEqualStructure($sourceDocument, $destinationDocument)) {
                        $destinationRecord->setData($recordData);
                    } else {
                        $destinationRecord = $this->transformRecord($destinationRecord, $recordData);
                    }
                    $recordsToSave->addRecord($destinationRecord);
                }
                 $this->destination->saveRecords($destinationDocument->getName(), $recordsToSave);
            };
        }
        $this->progress->finish();
        return true;
    }

    /**
     * Volume check
     *
     * @return bool
     */
    protected function volume()
    {
        $result = true;
        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            if ($this->source->getRecordsCount($sourceName) != $this->destination->getRecordsCount($destinationName)) {
                $this->logger->warning('Mismatch of entities in the document: ' . $destinationName);
                $result = false;
            }
        }
        $this->progress->finish();
        return $result;
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->getDocumentList());
    }

    /**
     * @return array
     */
    protected function getDocumentList()
    {
        if (empty($this->document)) {
            $this->documents = [
                'core_store' => 'store',
                'core_store_group' => 'store_group',
                'core_website' => 'store_website'
            ];
        }
        return $this->documents;
    }

    /**
     * @param Resource\Record $destinationRecord
     * @param array $recordData
     * @return Resource\Record
     */
    protected function transformRecord($destinationRecord, $recordData)
    {
        foreach ($destinationRecord->getFields() as $recordField) {
            $destinationRecord->setValue($recordField, $recordData[$recordField]);
        }
        return $destinationRecord;
    }

    /**
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destDocument
     * @return bool
     */
    protected function haveEqualStructure(Resource\Document $sourceDocument, Resource\Document $destDocument)
    {
        $diff = array_diff_key(
            $sourceDocument->getStructure()->getFields(),
            $destDocument->getStructure()->getFields()
        );
        return empty($diff);
    }
}
