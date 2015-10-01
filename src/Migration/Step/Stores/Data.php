<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\App\Step\StageInterface;
use Migration\Resource;
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
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Helper $helper
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start(count($this->helper->getDocumentList()));
        $documents = $this->helper->getDocumentList();
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
