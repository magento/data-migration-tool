<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\App\Step\StageInterface;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\Step\Stores\Model\DocumentsList;

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
     * @var DocumentsList
     */
    protected $documentsList;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param DocumentsList $documentsList
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        DocumentsList $documentsList,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->documentsList = $documentsList;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('stores_map_file');
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $documents = $this->documentsList->getDocumentsMap();
        $this->progress->start(count($documents));
        foreach ($documents as $sourceDocName => $destDocName) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationDocument = $this->destination->getDocument($destDocName);
            $this->destination->clearDocument($destDocName);
            /** @var \Migration\RecordTransformer $recordTransformer */
            $recordTransformer = $this->recordTransformerFactory->create(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destinationDocument,
                    'mapReader' => $this->map
                ]
            );
            $recordTransformer->init();
            $pageNumber = 0;
            while (!empty($sourceRecords = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $recordsToSave = $destinationDocument->getRecords();
                foreach ($sourceRecords as $recordData) {
                    /** @var Record $sourceRecord */
                    $sourceRecord = $this->recordFactory->create(
                        ['document' => $sourceDocument, 'data' => $recordData]
                    );
                    /** @var Record $destRecord */
                    $destinationRecord = $this->recordFactory->create(['document' => $destinationDocument]);
                    $recordTransformer->transform($sourceRecord, $destinationRecord);
                    $recordsToSave->addRecord($destinationRecord);
                }
                $this->destination->saveRecords($destinationDocument->getName(), $recordsToSave);
            };
        }
        $this->progress->finish();
        return true;
    }
}
