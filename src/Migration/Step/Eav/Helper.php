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
     * Get added groups
     *
     * @return \Migration\ResourceModel\Record[]
     */
    public function getAddedGroups()
    {
        return $this->addedGroups;
    }

    /**
     * Set added groups
     *
     * @param array $addedGroups
     * @return void
     */
    public function setAddedGroups(array $addedGroups)
    {
        $this->addedGroups = $addedGroups;
    }

    /**
     * Get design attribute and groups data
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDesignAttributeAndGroupsData()
    {
        $scheduleGroupsMigrated = [];
        $catalogProductSetIdsMigrated = [];
        $catalogProductSetIdDefault = null;
        $customLayoutAttributeId = null;
        $customDesignAttributeId = null;
        $entityTypeIdCatalogProduct = $this->getDestinationRecords('eav_entity_type', ['entity_type_code'])
            ['catalog_product']['entity_type_id'];

        foreach ($this->getDestinationRecords('eav_attribute_set') as $record) {
            if ($entityTypeIdCatalogProduct == $record['entity_type_id']) {
                if ('Default' == $record['attribute_set_name']) {
                    $catalogProductSetIdDefault = $record['attribute_set_id'];
                } else {
                    $catalogProductSetIdsMigrated[] = $record['attribute_set_id'];
                }
            }
        }
        foreach ($this->getDestinationRecords('eav_attribute_group') as $group) {
            if ('schedule-design-update' == $group['attribute_group_code'] &&
                $catalogProductSetIdDefault != $group['attribute_set_id']
            ) {
                $scheduleGroupsMigrated[] = $group;
            }
        }
        foreach ($this->getDestinationRecords('eav_attribute') as $record) {
            if ($record['entity_type_id'] == $entityTypeIdCatalogProduct) {
                switch ($record['attribute_code']) {
                    case 'custom_layout':
                        $customLayoutAttributeId = $record['attribute_id'];
                        break;
                    case 'custom_design':
                        $customDesignAttributeId = $record['attribute_id'];
                        break;
                }
            }
        }

        return [
            'scheduleGroupsMigrated' => $scheduleGroupsMigrated,
            'catalogProductSetIdsMigrated' => $catalogProductSetIdsMigrated,
            'customLayoutAttributeId' => $customLayoutAttributeId,
            'customDesignAttributeId' => $customDesignAttributeId,
            'entityTypeIdCatalogProduct' => $entityTypeIdCatalogProduct
        ];
    }

    /**
     * Remove ignored attributes from source records
     *
     * @param array $sourceRecords
     * @return array
     */
    public function clearIgnoredAttributes($sourceRecords)
    {
        $ignoredAttributes = $this->getAttributesGroupCodes('ignore');
        foreach ($sourceRecords as $attrNum => $sourceAttribute) {
            if (in_array($sourceAttribute['attribute_code'], array_keys($ignoredAttributes))
                && in_array($sourceAttribute['entity_type_id'], $ignoredAttributes[$sourceAttribute['attribute_code']])
            ) {
                unset($sourceRecords[$attrNum]);
            }
        }
        return $sourceRecords;
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
