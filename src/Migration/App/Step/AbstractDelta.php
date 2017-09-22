<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;

abstract class AbstractDelta implements StageInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapInterface
     */
    protected $mapReader;

    /**
     * @var []
     */
    protected $deltaDocuments;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var string
     */
    protected $mapConfigOption;

    /**
     * @var string
     */
    protected $groupName;

    /**
     * @var bool
     */
    protected $eolOnce = false;

    /**
     * @var array
     */
    protected $documentsDuplicateOnUpdate = [];

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory
    ) {
        $this->source = $source;
        $this->mapReader = $mapFactory->create($this->mapConfigOption);
        $this->deltaDocuments = $groupsFactory->create('delta_document_groups_file')->getGroup($this->groupName);
        $this->logger = $logger;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
    }

    /**
     * @return bool
     * @throws \Migration\Exception
     */
    public function perform()
    {
        $sourceDocuments = array_flip($this->source->getDocumentList());
        foreach ($this->deltaDocuments as $documentName => $idKey) {
            if (!$this->source->getDocument($documentName)) {
                continue;
            }
            $deltaLogName = $this->source->getDeltaLogName($documentName);
            if (!isset($sourceDocuments[$deltaLogName])) {
                throw new \Migration\Exception(sprintf('Deltalog for %s is not installed', $documentName));
            }

            $destinationName = $this->mapReader->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }

            if ($this->source->getRecordsCount($deltaLogName) == 0) {
                continue;
            }
            $this->logger->debug(sprintf('%s has changes', $documentName));

            $this->processDeletedRecords($documentName, $idKey, $destinationName);
            $this->processChangedRecords($documentName, $idKey);
        }
        return true;
    }

    /**
     * Mark processed records for deletion
     *
     * @param string $documentName
     * @param string $idKey
     * @param [] $ids
     * @return void
     */
    protected function markRecordsProcessed($documentName, $idKey, $ids)
    {
        $ids = implode("','", $ids);
        /** @var ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $adapter->updateDocument($documentName, ['processed' => 1], "`$idKey` in ('$ids')");
    }

    /**
     * @param string $documentName
     * @param string $idKey
     * @param string $destinationName
     * @return void
     */
    protected function processDeletedRecords($documentName, $idKey, $destinationName)
    {
        $this->destination->getAdapter()->setForeignKeyChecks(1);
        while (!empty($items = $this->source->getDeletedRecords($documentName, $idKey))) {
            $this->destination->deleteRecords(
                $this->destination->addDocumentPrefix($destinationName),
                $idKey,
                $items
            );
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKey, $items);
        }
        $this->destination->getAdapter()->setForeignKeyChecks(0);
    }

    /**
     * @param string $documentName
     * @param string $idKey
     * @return void
     */
    protected function processChangedRecords($documentName, $idKey)
    {
        $items = $this->source->getChangedRecords($documentName, $idKey);
        if (empty($items)) {
            return;
        }
        if (!$this->eolOnce) {
            $this->eolOnce = true;
            echo PHP_EOL;
        }
        $destinationName = $this->mapReader->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);

        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);

        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
        do {
            $destinationRecords = $destDocument->getRecords();

            $ids = [];

            foreach ($items as $data) {
                echo('.');
                $ids[] = $data[$idKey];

                $this->transformData(
                    $data,
                    $sourceDocument,
                    $destDocument,
                    $recordTransformer,
                    $destinationRecords
                );
            }
            $fieldsUpdateOnDuplicate = (!empty($this->documentsDuplicateOnUpdate[$destinationName]))
                ? $this->documentsDuplicateOnUpdate[$destinationName]
                : false;
            $this->destination->updateChangedRecords($destinationName, $destinationRecords, $fieldsUpdateOnDuplicate);
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKey, $ids);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
    }

    /**
     * @param array $data
     * @param ResourceModel\Document $sourceDocument
     * @param ResourceModel\Document $destDocument
     * @param \Migration\RecordTransformer $recordTransformer
     * @param ResourceModel\Record\Collection $destinationRecords
     * @return void
     */
    protected function transformData($data, $sourceDocument, $destDocument, $recordTransformer, $destinationRecords)
    {
        if ($recordTransformer) {
            $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
            $destRecord = $this->recordFactory->create(['document' => $destDocument]);
            $recordTransformer->transform($record, $destRecord);
        } else {
            $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
        }
        $destinationRecords->addRecord($destRecord);
    }

    /**
     * @param ResourceModel\Document $sourceDocument
     * @param ResourceModel\Document $destinationDocument
     * @return \Migration\RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        $recordTransformer = $this->recordTransformerFactory->create(
            [
                'sourceDocument' => $sourceDocument,
                'destDocument' => $destinationDocument,
                'mapReader' => $this->mapReader
            ]
        );
        $recordTransformer->init();
        return $recordTransformer;
    }
}
