<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;

/**
 * Class AbstractDelta
 */
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
     * Perform
     *
     * @return bool
     * @throws \Migration\Exception
     */
    public function perform()
    {
        $sourceDocuments = array_flip($this->source->getDocumentList());
        foreach ($this->deltaDocuments as $documentName => $idKeys) {
            $idKeys = explode(',', $idKeys);
            if (!$this->source->getDocument($documentName)) {
                continue;
            }
            $deltaLogName = $this->source->getDeltaLogName($documentName);
            if (!isset($sourceDocuments[$deltaLogName])) {
                throw new \Migration\Exception(sprintf('Deltalog for %s is not installed', $documentName));
            }
            $destinationName = $this->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            if ($this->source->getRecordsCount($deltaLogName) == 0) {
                continue;
            }
            $this->logger->debug(sprintf('%s has changes', $documentName));
            if (!$this->eolOnce) {
                $this->eolOnce = true;
                echo PHP_EOL;
            }
            $this->processDeletedRecords($documentName, $idKeys, $destinationName);
            $this->processChangedRecords($documentName, $idKeys);
        }
        return true;
    }

    /**
     * Mark processed records for deletion
     *
     * @param string $documentName
     * @param array $idKeys
     * @param [] $ids
     * @return void
     */
    protected function markRecordsProcessed($documentName, $idKeys, $items)
    {
        /** @var ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        if (count($idKeys) == 1) {
            $idKey = array_shift($idKeys);
            $items = array_column($items, $idKey);
            $items = implode("','", $items);
            $adapter->updateDocument($documentName, ['processed' => 1], "`$idKey` IN ('$items')");
        } else if (count($idKeys) > 1 && is_array($items)) {
            foreach ($items as $item) {
                $andFields = [];
                foreach ($idKeys as $idKey) {
                    $andFields[] = "$idKey = $item[$idKey]";
                }
                $adapter->updateDocument($documentName, ['processed' => 1], implode(' AND ', $andFields));
            }
        }
    }

    /**
     * Process deleted records
     *
     * @param string $documentName
     * @param array $idKeys
     * @param string $destinationName
     * @return void
     */
    protected function processDeletedRecords($documentName, $idKeys, $destinationName)
    {
        $this->destination->getAdapter()->setForeignKeyChecks(1);
        while (!empty($items = $this->source->getDeletedRecords($documentName, $idKeys))) {
            echo('.');
            $this->destination->deleteRecords(
                $this->destination->addDocumentPrefix($destinationName),
                $idKeys,
                $items
            );
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
        }
        $this->destination->getAdapter()->setForeignKeyChecks(0);
    }

    /**
     * Process changed records
     *
     * @param string $documentName
     * @param array $idKeys
     * @return void
     */
    protected function processChangedRecords($documentName, $idKeys)
    {
        $destinationName = $this->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);
        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);
        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
        while (!empty($items = $this->source->getChangedRecords($documentName, $idKeys))) {
            $destinationRecords = $destDocument->getRecords();
            foreach ($items as $data) {
                echo('.');
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
            $this->updateChangedRecords($destinationName, $destinationRecords, $fieldsUpdateOnDuplicate);
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
        };
    }

    /**
     * Update changed records
     *
     * @param $destinationName
     * @param $destinationRecords
     * @param $fieldsUpdateOnDuplicate
     */
    protected function updateChangedRecords($destinationName, $destinationRecords, $fieldsUpdateOnDuplicate)
    {
        $this->destination->updateChangedRecords($destinationName, $destinationRecords, $fieldsUpdateOnDuplicate);
    }

    /**
     * Transform data
     *
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
     * Get record transformer
     *
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

    /**
     * Get document map
     *
     * @param string $document
     * @param string $type
     * @return mixed
     */
    protected function getDocumentMap($document, $type)
    {
        return $this->mapReader->getDocumentMap($document, $type);
    }
}
