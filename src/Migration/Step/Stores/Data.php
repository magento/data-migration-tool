<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\App\Step\StageInterface;
use Migration\ResourceModel;
use Migration\App\ProgressBar;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory
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
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
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
                    /** @var ResourceModel\Record $destinationRecord */
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
     * @param ResourceModel\Record $destinationRecord
     * @param array $recordData
     * @return ResourceModel\Record
     */
    protected function transformRecord($destinationRecord, $recordData)
    {
        foreach ($destinationRecord->getFields() as $recordField) {
            $destinationRecord->setValue($recordField, $recordData[$recordField]);
        }
        return $destinationRecord;
    }

    /**
     * @param ResourceModel\Document $sourceDocument
     * @param ResourceModel\Document $destDocument
     * @return bool
     */
    protected function haveEqualStructure(ResourceModel\Document $sourceDocument, ResourceModel\Document $destDocument)
    {
        $diff = array_diff_key(
            $sourceDocument->getStructure()->getFields(),
            $destDocument->getStructure()->getFields()
        );
        return empty($diff);
    }
}
