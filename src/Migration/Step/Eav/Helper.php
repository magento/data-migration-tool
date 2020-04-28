<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Reader\MapInterface;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\RecordTransformer;
use Migration\RecordTransformerFactory;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Document;
use Migration\ResourceModel\Source;
use Migration\Reader\GroupsFactory;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Map
     */
    protected $map;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var RecordTransformerFactory
     */
    protected $factory;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerAttributes;

    /**
     * @var \Migration\ResourceModel\Record[]
     */
    protected $addedGroups;

    /**
     * @param MapFactory $mapFactory
     * @param Source $source
     * @param Destination $destination
     * @param RecordTransformerFactory $factory
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        MapFactory $mapFactory,
        Source $source,
        Destination $destination,
        RecordTransformerFactory $factory,
        GroupsFactory $groupsFactory
    ) {
        $this->map = $mapFactory->create('eav_map_file');
        $this->source = $source;
        $this->destination = $destination;
        $this->factory = $factory;
        $this->readerGroups = $groupsFactory->create('eav_document_groups_file');
        $this->readerAttributes = $groupsFactory->create('eav_attribute_groups_file');
    }

    /**
     * Get source records count
     *
     * @param string $sourceDocumentName
     * @return int
     */
    public function getSourceRecordsCount($sourceDocumentName)
    {
        return $this->source->getRecordsCount($sourceDocumentName);
    }

    /**
     * Get destination records count
     *
     * @param string $sourceDocumentName
     * @return int
     */
    public function getDestinationRecordsCount($sourceDocumentName)
    {
        return $this->destination->getRecordsCount(
            $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE)
        );
    }

    /**
     * Get destination records
     *
     * @param string $sourceDocName
     * @param array $keyFields
     * @return array
     */
    public function getDestinationRecords($sourceDocName, $keyFields = [])
    {
        $destinationDocumentName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
        $data = [];
        $count = $this->destination->getRecordsCount($destinationDocumentName);
        foreach ($this->destination->getRecords($destinationDocumentName, 0, $count) as $row) {
            if ($keyFields) {
                $key = [];
                foreach ($keyFields as $keyField) {
                    $key[] = $row[$keyField];
                }
                $data[implode('-', $key)] = $row;
            } else {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * Get source records
     *
     * @param string $sourceDocName
     * @param array $keyFields
     * @return array
     */
    public function getSourceRecords($sourceDocName, $keyFields = [])
    {
        $data = [];
        $count = $this->source->getRecordsCount($sourceDocName);
        foreach ($this->source->getRecords($sourceDocName, 0, $count) as $row) {
            if ($keyFields) {
                $key = [];
                foreach ($keyFields as $keyField) {
                    $key[] = $row[$keyField];
                }
                $data[implode('-', $key)] = $row;
            } else {
                $data[] = $row;
            }
        }

        return $data;
    }

    /**
     * Get record transformer
     *
     * @param Document $sourceDocument
     * @param Document $destinationDocument
     * @return RecordTransformer
     */
    public function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->factory->create([
            'sourceDocument' => $sourceDocument,
            'destDocument' => $destinationDocument,
            'mapReader' => $this->map
        ])->init();
    }

    /**
     * Delete backed up documents
     *
     * @return void
     */
    public function deleteBackups()
    {
        foreach (array_keys($this->readerGroups->getGroup('documents')) as $documentName) {
            $documentName = $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);
            if ($documentName) {
                $this->destination->deleteDocumentBackup($documentName);
            }
        }
    }

    /**
     * Retrieves attribute codes with types of $groupName group
     *
     * @param string $groupName
     * @return array
     */
    public function getAttributesGroupCodes($groupName)
    {
        $entityTypesCodeToId = $this->getEntityTypesCodeToId();
        $attributeCodes = $this->readerAttributes->getGroup($groupName);
        foreach ($attributeCodes as $attributeCode => $attributeTypes) {
            $attributeCodes[$attributeCode] = [];
            foreach ($attributeTypes as $attributeType) {
                if (array_key_exists($attributeType, $entityTypesCodeToId)) {
                    $attributeCodes[$attributeCode][] = $entityTypesCodeToId[$attributeType];
                }
            }
        }
        return $attributeCodes;
    }

    /**
     * Get entity types code to id
     *
     * @return array
     */
    private function getEntityTypesCodeToId()
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->source->getAdapter()->getSelect();
        $select->from($this->source->addDocumentPrefix('eav_entity_type'), ['entity_type_code', 'entity_type_id']);
        $entityTypeIds = $select->getAdapter()->fetchPairs($select);
        return $entityTypeIds;
    }
}
