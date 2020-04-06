<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\App\Step\StageInterface;
use Migration\App\Step\RollbackInterface;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Document;
use Migration\ResourceModel\RecordFactory;
use Migration\ResourceModel\Source;
use Migration\Step\Eav\Model\IgnoredAttributes;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @codeCoverageIgnoreStart
 */
class Data implements StageInterface, RollbackInterface
{
    /**
     * @var array;
     */
    protected $newAttributeSets = [];

    /**
     * @var array;
     */
    protected $mapAttributeIdsDestOldNew = [];

    /**
     * @var array;
     */
    protected $mapAttributeIdsSourceDest = [];

    /**
     * @var array;
     */
    protected $mapAttributeSetIdsDestOldNew = [];

    /**
     * @var array;
     */
    protected $mapAttributeGroupIdsDestOldNew = [];

    /**
     * @var array;
     */
    protected $mapEntityTypeIdsDestOldNew = [];

    /**
     * @var array;
     */
    protected $mapEntityTypeIdsSourceDest = [];

    /**
     * @var array;
     */
    protected $defaultAttributeSetIds = [];

    /**
     * @var array;
     */
    protected $mapAttributeGroupIdsSourceDest = [];

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var RecordFactory
     */
    protected $factory;

    /**
     * @var InitialData
     */
    protected $initialData;

    /**
     * @var IgnoredAttributes
     */
    protected $ignoredAttributes;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var array
     */
    protected $groupsDataToAdd = [
        [
            'attribute_group_name' => 'Schedule Design Update',
            'attribute_group_code' => 'schedule-design-update',
            'sort_order' => '55',
        ], [
            'attribute_group_name' => 'Bundle Items',
            'attribute_group_code' => 'bundle-items',
            'sort_order' => '16',
        ]
    ];

    /**
     * Attributes will be added to attribute group if not exist
     *
     * @var array
     */
    private $attributesGroupToAdd = [
        'category_ids' => 'product-details',
        'price_type' => 'product-details',
        'sku_type' => 'product-details',
        'weight_type' => 'product-details',
        'giftcard_type' => 'product-details',
        'quantity_and_stock_status' => 'product-details',
        'swatch_image' => 'image-management'
    ];

    private $excludedProductAttributeGroups = [
        'General',
        'Prices',
        'Recurring Profile'
    ];

    private $mapProductAttributeGroupNamesSourceDest = [
        'General' => 'Product Details',
        'Prices' => 'Product Details',
        'Recurring Profile' => 'Product Details'
    ];

    private const ATTRIBUTE_SETS_ALL = 'all';
    private const ATTRIBUTE_SETS_DEFAULT = 'default';
    private const ATTRIBUTE_SETS_NONE_DEFAULT = 'none_default';

    private const ENTITY_TYPE_PRODUCT_CODE = 'catalog_product';
    private const ENTITY_TYPE_CATEGORY_CODE = 'catalog_category';
    private const ENTITY_TYPE_CUSTOMER_CODE = 'customer';
    private const ENTITY_TYPE_CUSTOMER_ADDRESS_CODE = 'customer_address';

    /**
     * @param Source $source
     * @param Destination $destination
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Helper $helper
     * @param RecordFactory $factory
     * @param InitialData $initialData
     * @param IgnoredAttributes $ignoredAttributes
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Source $source,
        Destination $destination,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Helper $helper,
        RecordFactory $factory,
        InitialData $initialData,
        IgnoredAttributes $ignoredAttributes,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('eav_map_file');
        $this->readerGroups = $groupsFactory->create('eav_document_groups_file');
        $this->helper = $helper;
        $this->factory = $factory;
        $this->initialData = $initialData;
        $this->ignoredAttributes = $ignoredAttributes;
        $this->progress = $progress;
    }

    /**
     * Entry point. Run migration of EAV structure.
     *
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->migrateEntityTypes();
        $this->migrateAttributeSets();
        $this->createProductAttributeSetStructures();
        $this->migrateCustomProductAttributeGroups();
        $this->migrateAttributes();
        $this->migrateAttributesExtended();
        $this->migrateCustomEntityAttributes();
        $this->progress->finish();
        return true;
    }

    /**
     * Migrate Entity Type table
     *
     * @return void
     */
    protected function migrateEntityTypes()
    {
        $documentName = 'eav_entity_type';
        $mappingField = 'entity_type_code';

        $this->progress->advance();
        $sourceDocument = $this->source->getDocument($documentName);
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
        );
        $this->destination->backupDocument($destinationDocument->getName());
        $destinationRecords = $this->helper->getDestinationRecords($documentName, [$mappingField]);
        $recordsToSave = $destinationDocument->getRecords();
        $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
        foreach ($this->helper->getSourceRecords($documentName) as $recordData) {
            /** @var Record $sourceRecord */
            $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $recordData]);
            /** @var Record $destinationRecord */
            $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
            $mappingValue = $sourceRecord->getValue($mappingField);
            if (isset($destinationRecords[$mappingValue])) {
                $destinationRecordData = $destinationRecords[$mappingValue];
                unset($destinationRecords[$mappingValue]);
            } else {
                $destinationRecordData = $destinationRecord->getDataDefault();
            }
            $destinationRecord->setData($destinationRecordData);
            $recordTransformer->transform($sourceRecord, $destinationRecord);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->destination->clearDocument($destinationDocument->getName());
        $this->saveRecords($destinationDocument, $recordsToSave);

        $recordsToSave = $destinationDocument->getRecords();
        foreach ($destinationRecords as $record) {
            $record['entity_type_id'] = null;
            $destinationRecord = $this->factory->create([
                'document' => $destinationDocument,
                'data' => $record
            ]);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->saveRecords($destinationDocument, $recordsToSave);
        $this->createMapEntityTypeIds();
    }

    /**
     * Migrate eav_attribute_set and eav_attribute_group
     *
     * @return void
     */
    protected function migrateAttributeSetsAndGroups()
    {
        foreach (['eav_attribute_set', 'eav_attribute_group'] as $documentName) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($documentName);
            $destinationDocument = $this->destination->getDocument(
                $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
            );

            $this->destination->backupDocument($destinationDocument->getName());

            $sourceRecords = $this->source->getRecords($documentName, 0, $this->source->getRecordsCount($documentName));
            $recordsToSave = $destinationDocument->getRecords();
            $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
            foreach ($sourceRecords as $recordData) {
                $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $recordData]);
                $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
                $recordTransformer->transform($sourceRecord, $destinationRecord);
                $recordsToSave->addRecord($destinationRecord);
            }

            if ($documentName == 'eav_attribute_set') {
                foreach ($this->initialData->getAttributeSets('dest') as $record) {
                    $record['attribute_set_id'] = null;
                    $record['entity_type_id'] = $this->mapEntityTypeIdsDestOldNew[$record['entity_type_id']];
                    $destinationRecord = $this->factory->create(
                        [
                            'document' => $destinationDocument,
                            'data' => $record
                        ]
                    );
                    $recordsToSave->addRecord($destinationRecord);
                }
            }

            if ($documentName == 'eav_attribute_group') {
                foreach ($this->initialData->getAttributeGroups('dest') as $record) {
                    $oldAttributeSet = $this->initialData->getAttributeSets('dest')[$record['attribute_set_id']];
                    $entityTypeId = $this->mapEntityTypeIdsDestOldNew[$oldAttributeSet['entity_type_id']];
                    $newAttributeSet = $this->newAttributeSets[
                        $entityTypeId . '-' . $oldAttributeSet['attribute_set_name']
                    ];
                    $record['attribute_set_id'] = $newAttributeSet['attribute_set_id'];

                    $record['attribute_group_id'] = null;
                    $destinationRecord = $this->factory->create(
                        [
                            'document' => $destinationDocument,
                            'data' => $record
                        ]
                    );
                    $recordsToSave->addRecord($destinationRecord);
                }
                $recordsToSave = $this->addAttributeGroups($recordsToSave, $documentName, $this->groupsDataToAdd);
            }

            $this->destination->clearDocument($destinationDocument->getName());
            $this->saveRecords($destinationDocument, $recordsToSave);
            if ($documentName == 'eav_attribute_set') {
                $this->createMapAttributeSetIds();
            }
            if ($documentName == 'eav_attribute_group') {
                $this->createMapAttributeGroupIds();
            }
        }
    }

    private function migrateAttributeSets()
    {
        $documentName = 'eav_attribute_set';
        $this->progress->advance();
        $sourceDocument = $this->source->getDocument($documentName);
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
        );
        $this->destination->backupDocument($destinationDocument->getName());
        $sourceRecords = $this->source->getRecords($documentName, 0, $this->source->getRecordsCount($documentName));
        $recordsToSave = $destinationDocument->getRecords();
        $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
        foreach ($sourceRecords as $recordData) {
            $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $recordData]);
            $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
            $recordTransformer->transform($sourceRecord, $destinationRecord);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->destination->clearDocument($destinationDocument->getName());
        $this->saveRecords($destinationDocument, $recordsToSave);
        $this->createMapAttributeSetIds();
    }

    private function createProductAttributeSetStructures()
    {
        // update mapped keys
        $documentName = 'eav_attribute_group';
        $this->destination->backupDocument($documentName);
        $this->updateMappedKeys(
            $documentName,
            'attribute_set_id',
            $this->helper->getDestinationRecords($documentName),
            $this->mapAttributeSetIdsDestOldNew
        );
        // add attribute groups from Magento 2 for each attribute set from Magento 1
        $prototypeProductAttributeGroups = $this->getDefaultProductAttributeGroups();
        $productAttributeSets = $this->getAttributeSets(
            $this->getEntityTypeIdByCode(self::ENTITY_TYPE_PRODUCT_CODE),
            self::ATTRIBUTE_SETS_NONE_DEFAULT
        );
        foreach ($productAttributeSets as $attributeSet) {
            foreach ($prototypeProductAttributeGroups as &$prototypeAttributeGroup) {
                $prototypeAttributeGroup['attribute_set_id'] = $attributeSet['attribute_set_id'];
            }
            $this->saveRecords($documentName, $prototypeProductAttributeGroups);
        }

        // update mapped keys
        $entityAttributeDocument = 'eav_entity_attribute';
        $this->destination->backupDocument($documentName);
        $this->updateMappedKeys(
            $entityAttributeDocument,
            'attribute_set_id',
            $this->helper->getDestinationRecords($entityAttributeDocument),
            $this->mapAttributeSetIdsDestOldNew
        );
        // add entity attributes from Magento 2 for each attribute set from Magento 1
        foreach ($productAttributeSets as $attributeSet) {
            $prototypeProductEntityAttributes = $this->getDefaultProductEntityAttributes();
            foreach ($prototypeProductEntityAttributes as &$prototypeEntityAttribute) {
                $attributeGroupId = $this->getAttributeGroupIdForAttributeSet(
                    $prototypeEntityAttribute['attribute_group_id'],
                    $attributeSet['attribute_set_id']
                );
                $prototypeEntityAttribute['attribute_set_id'] = $attributeSet['attribute_set_id'];
                $prototypeEntityAttribute['attribute_group_id'] = $attributeGroupId;
            }
            $this->saveRecords($entityAttributeDocument, $prototypeProductEntityAttributes);
        }
    }

    public function updateMappedKeys($destDocument, $column, $records, $mappedIdKeys)
    {
        if (array_values($mappedIdKeys) == array_values(array_keys($mappedIdKeys))) {
            return;
        }
        foreach ($records as &$record) {
            if (empty($mappedIdKeys[$record[$column]])) {
                throw new \Migration\Exception(
                    sprintf('Not mapped id key %s found for %s.%s ', $record[$column], $destDocument, $column)
                );
            }
            $record[$column] = $mappedIdKeys[$record[$column]];
        }
        $this->destination->clearDocument($destDocument);
        $this->saveRecords($destDocument, $records);
    }

    public function getAttributeSets($entityTypeId = null, $mode = self::ATTRIBUTE_SETS_ALL)
    {
        $attributeSets = [];
        foreach ($this->initialData->getAttributeSets('source') as $attributeSet) {
            if ($entityTypeId === null
                || $entityTypeId == $attributeSet['entity_type_id']
            ) {
                $attributeSets[$attributeSet['attribute_set_id']] = $attributeSet;
            }
        }
        if ($mode == self::ATTRIBUTE_SETS_DEFAULT) {
            return array_shift($attributeSets);
        } else if ($mode == self::ATTRIBUTE_SETS_NONE_DEFAULT) {
            array_shift($attributeSets);
            return $attributeSets;
        }
        return $attributeSets;
    }

    public function getEntityTypeIdByCode($code)
    {
        $entityTypeId = null;
        foreach ($this->initialData->getEntityTypes('source') as $entityType) {
            if ($entityType['entity_type_code'] == $code) {
                $entityTypeId = $entityType['entity_type_id'];
            }
        }
        return $entityTypeId;
    }

    public function getDefaultProductAttributeGroups()
    {
        $defaultProductAttributeSetId = $this->getAttributeSets(
            $this->getEntityTypeIdByCode(self::ENTITY_TYPE_PRODUCT_CODE),
            self::ATTRIBUTE_SETS_DEFAULT
        )['attribute_set_id'];
        $attributeGroups = [];
        foreach ($this->initialData->getAttributeGroups('dest') as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $defaultProductAttributeSetId) {
                $attributeGroup['attribute_group_id'] = null;
                $attributeGroup['attribute_set_id'] = null;
                $attributeGroups[] = $attributeGroup;
            }
        }
        return $attributeGroups;
    }

    public function getDefaultProductEntityAttributes()
    {
        $defaultProductAttributeSetId = $this->getAttributeSets(
            $this->getEntityTypeIdByCode(self::ENTITY_TYPE_PRODUCT_CODE),
            self::ATTRIBUTE_SETS_DEFAULT
        )['attribute_set_id'];
        $entityAttributes = [];
        foreach ($this->initialData->getEntityAttributes('dest') as $entityAttribute) {
            if ($entityAttribute['attribute_set_id'] == $defaultProductAttributeSetId) {
                $entityAttribute['entity_attribute_id'] = null;
                $entityAttribute['attribute_set_id'] = null;
                $entityAttributes[] = $entityAttribute;
            }
        }
        return $entityAttributes;
    }

    public function getAttributeGroupIdForAttributeSet($prototypeAttributeGroupId, $attributeSetId)
    {
        $attributeGroupId = null;
        $attributeGroupCode = $this->getAttributeGroupCodeFromId($prototypeAttributeGroupId);
        foreach ($this->helper->getDestinationRecords('eav_attribute_group') as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $attributeSetId
                && $attributeGroup['attribute_group_code'] == $attributeGroupCode
            ) {
                $attributeGroupId = $attributeGroup['attribute_group_id'];
            }
        }
        return $attributeGroupId;
    }

    public function getAttributeGroupCodeFromId($attributeGroupId)
    {
        $attributeGroupCode = null;
        foreach ($this->initialData->getAttributeGroups('dest') as $attributeGroup) {
            if ($attributeGroup['attribute_group_id'] == $attributeGroupId) {
                $attributeGroupCode = $attributeGroup['attribute_group_code'];
            }
        }
        return $attributeGroupCode;
    }

    public function migrateCustomProductAttributeGroups()
    {
        $productAttributeSets = $this->getAttributeSets(
            $this->getEntityTypeIdByCode(self::ENTITY_TYPE_PRODUCT_CODE),
            self::ATTRIBUTE_SETS_ALL
        );
        foreach ($productAttributeSets as $productAttributeSet) {
            $attributeGroupIds = $this->getCustomProductAttributeGroups(
                $productAttributeSet['attribute_set_id']
            );
            if ($attributeGroupIds) {
                $this->migrateAttributeGroups($attributeGroupIds);
            }
        }
        $this->createMapProductAttributeGroupIds();
    }

    public function getCustomProductAttributeGroups($attributeSetId)
    {
        $defaultAttributeGroupNames = [];
        $sourceAttributeGroupNames = [];
        foreach ($this->getDefaultProductAttributeGroups() as $attributeGroup) {
            $defaultAttributeGroupNames[] = $attributeGroup['attribute_group_name'];
        }
        foreach ($this->helper->getSourceRecords('eav_attribute_group') as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $attributeSetId
                && !in_array($attributeGroup['attribute_group_name'], $this->excludedProductAttributeGroups)
            ) {
                $sourceAttributeGroupNames[$attributeGroup['attribute_group_id']]
                    = $attributeGroup['attribute_group_name'];
            }
        }
        return array_keys(array_diff($sourceAttributeGroupNames, $defaultAttributeGroupNames));
    }

    private function migrateAttributeGroups($attributeGroupIds)
    {
        $documentName = 'eav_attribute_group';
        $this->progress->advance();
        $sourceDocument = $this->source->getDocument($documentName);
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
        );
        $sourceRecords = $this->source->getRecords(
            $documentName,
            0,
            $this->source->getRecordsCount($documentName),
            new \Zend_Db_Expr(sprintf('attribute_group_id IN (%s)', implode(',', $attributeGroupIds)))
        );
        $recordsToSave = $destinationDocument->getRecords();
        $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
        foreach ($sourceRecords as $recordData) {
            $recordData['attribute_group_id'] = null;
            $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $recordData]);
            $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
            $recordTransformer->transform($sourceRecord, $destinationRecord);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->saveRecords($destinationDocument, $recordsToSave);
    }

    /**
     * Add attribute groups to Magento 1 which are needed for Magento 2
     *
     * @param Record\Collection $recordsToSave
     * @param string $documentName
     * @param array $groupsData
     * @return Record\Collection
     */
    protected function addAttributeGroups($recordsToSave, $documentName, array $groupsData)
    {
        $entityTypeIdCatalogProduct = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_code'])
            ['catalog_product']['entity_type_id'];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->source->getAdapter()->getSelect();
        $select->from(
            ['eas' => $this->source->addDocumentPrefix('eav_attribute_set')],
            ['attribute_set_id']
        )->where(
            'entity_type_id = ?',
            $entityTypeIdCatalogProduct
        );
        $catalogProductSetIds = $select->getAdapter()->fetchCol($select);
        $addedGroups = [];
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
        );
        foreach ($groupsData as $group) {
            foreach ($catalogProductSetIds as $id) {
                $destinationRecord = $this->factory->create(
                    [
                        'document' => $destinationDocument,
                        'data' => [
                            'attribute_group_id' => null,
                            'attribute_set_id' => $id,
                            'attribute_group_name' => $group['attribute_group_name'],
                            'sort_order' => $group['sort_order'],
                            'default_id' => '0',
                            'attribute_group_code' => $group['attribute_group_code'],
                            'tab_group_code' => 'advanced',
                        ]
                    ]
                );
                $addedGroups[] = $destinationRecord;
                $recordsToSave->addRecord($destinationRecord);
            }
        }
        $this->helper->setAddedGroups($addedGroups);

        return $recordsToSave;
    }

    /**
     * Change old default attribute set ids in entity types
     *
     * @param array $exceptions
     * @return void
     */
    protected function changeOldAttributeSetIdsInEntityTypes(array $exceptions)
    {
        $documentName = 'eav_entity_type';
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
        );
        $recordsToSave = $destinationDocument->getRecords();
        $entityTypesMigrated = $this->helper->getDestinationRecords($destinationDocument->getName());
        foreach ($entityTypesMigrated as $record) {
            if (isset($this->defaultAttributeSetIds[$record['entity_type_id']])
                && !in_array($record['entity_type_code'], $exceptions)
            ) {
                $record['default_attribute_set_id'] =
                    $this->defaultAttributeSetIds[$record['entity_type_id']];
            }
            $destinationRecord = $this->factory->create(
                [
                    'document' => $destinationDocument,
                    'data' => $record
                ]
            );
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->destination->clearDocument($destinationDocument->getName());
        $this->saveRecords($destinationDocument, $recordsToSave);
    }

    /**
     * Migrate eav_attribute
     *
     * @return void
     */
    protected function migrateAttributes()
    {
        $this->progress->advance();
        $sourceDocName = 'eav_attribute';
        $sourceDocument = $this->source->getDocument($sourceDocName);
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE)
        );
        $this->destination->backupDocument($destinationDocument->getName());
        $sourceRecords = $this->ignoredAttributes->clearIgnoredAttributes($this->initialData->getAttributes('source'));
        $destinationRecords = $this->helper->getDestinationRecords(
            $sourceDocName,
            ['entity_type_id', 'attribute_code']
        );
        $recordsToSave = $destinationDocument->getRecords();
        $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
        foreach ($sourceRecords as $sourceRecordData) {
            /** @var Record $sourceRecord */
            $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $sourceRecordData]);
            /** @var Record $destinationRecord */
            $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
            $mappedKey = null;
            $entityTypeId = $sourceRecord->getValue('entity_type_id');
            if (isset($this->mapEntityTypeIdsSourceDest[$entityTypeId])) {
                $mappedId = $this->mapEntityTypeIdsSourceDest[$entityTypeId];
                $mappedKey = $mappedId . '-' . $sourceRecord->getValue('attribute_code');
            }
            if ($mappedKey && isset($destinationRecords[$mappedKey])) {
                $destinationRecordData = $destinationRecords[$mappedKey];
                $destinationRecordData['attribute_id'] = $sourceRecordData['attribute_id'];
                $destinationRecordData['entity_type_id'] = $sourceRecordData['entity_type_id'];
                $destinationRecord->setData($destinationRecordData);
                unset($destinationRecords[$mappedKey]);
            } else {
                $destinationRecordData = $destinationRecord->getDataDefault();
                $destinationRecord->setData($destinationRecordData);
                $recordTransformer->transform($sourceRecord, $destinationRecord);
            }
            $recordsToSave->addRecord($destinationRecord);
        }

        foreach ($destinationRecords as $record) {
            /** @var Record $destinationRecord */
            $destinationRecord = $this->factory->create(['document' => $destinationDocument, 'data' => $record]);
            $destinationRecord->setValue('attribute_id', null);
            $destinationRecord->setValue(
                'entity_type_id',
                $this->mapEntityTypeIdsDestOldNew[$destinationRecord->getValue('entity_type_id')]
            );
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->destination->clearDocument($destinationDocument->getName());
        $this->saveRecords($destinationDocument, $recordsToSave);
        $this->createMapAttributeIds();
        $mapAttributeIdsDestOldNew = [];
        foreach ($destinationRecords as $record) {
            if (isset($this->mapAttributeIdsDestOldNew[$record['attribute_id']])) {
                $mapAttributeIdsDestOldNew[$record['attribute_id']] =
                    $this->mapAttributeIdsDestOldNew[$record['attribute_id']];
            }
        }
        $mapAttributeIds = array_flip($this->mapAttributeIdsSourceDest);
        $mapAttributeIds = array_replace($mapAttributeIds, $mapAttributeIdsDestOldNew);
        $this->updateMappedKeys(
            'eav_entity_attribute',
            'attribute_id',
            $this->helper->getDestinationRecords('eav_entity_attribute'),
            $mapAttributeIds
        );
    }

    /**
     * Migrate eav_entity_attributes
     *
     * @return void
     */
    protected function migrateEntityAttributes()
    {
        $this->progress->advance();
        $sourceDocName = 'eav_entity_attribute';
        $sourceDocument = $this->source->getDocument($sourceDocName);
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE)
        );
        $this->destination->backupDocument($destinationDocument->getName());
        $recordsToSave = $destinationDocument->getRecords();
        $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
        foreach ($this->helper->getSourceRecords($sourceDocName) as $sourceRecordData) {
            $sourceRecord = $this->factory->create([
                'document' => $sourceDocument,
                'data' => $sourceRecordData
            ]);
            $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
            $recordTransformer->transform($sourceRecord, $destinationRecord);
            $recordsToSave->addRecord($destinationRecord);
        }

        foreach ($this->helper->getDestinationRecords('eav_entity_attribute') as $record) {
            if (!isset($this->mapAttributeIdsDestOldNew[$record['attribute_id']])
                || !isset($this->mapAttributeSetIdsDestOldNew[$record['attribute_set_id']])
                || !isset($this->mapAttributeGroupIdsDestOldNew[$record['attribute_group_id']])
                || !isset($this->mapEntityTypeIdsDestOldNew[$record['entity_type_id']])
            ) {
                continue;
            }
            $record['attribute_id'] = $this->mapAttributeIdsDestOldNew[$record['attribute_id']];
            $record['attribute_set_id'] = $this->mapAttributeSetIdsDestOldNew[$record['attribute_set_id']];
            $record['attribute_group_id'] = $this->mapAttributeGroupIdsDestOldNew[$record['attribute_group_id']];
            $record['entity_type_id'] = $this->mapEntityTypeIdsDestOldNew[$record['entity_type_id']];

            $record['entity_attribute_id'] = null;
            $destinationRecord = $this->factory->create(['document' => $destinationDocument, 'data' => $record]);
            $recordsToSave->addRecord($destinationRecord);
        }

        $recordsToSave = $this->processDesignEntityAttributes($recordsToSave);
        $recordsToSave = $this->moveProductAttributes($recordsToSave);
        $recordsToSave = $this->addLackingAttributesToCustomerAttributeSet($recordsToSave);

        $this->destination->clearDocument($destinationDocument->getName());
        $this->saveRecords($destinationDocument, $recordsToSave);
    }

    protected function migrateCustomEntityAttributes()
    {
        $this->progress->advance();
        $sourceDocName = 'eav_entity_attribute';
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE)
        );
        $recordsToSave = $destinationDocument->getRecords();
        $customAttributeIds = $this->getCustomAttributeIds();
        $customEntityAttributes = $this->source->getRecords(
            $sourceDocName,
            0,
            $this->source->getRecordsCount($sourceDocName),
            new \Zend_Db_Expr(sprintf('attribute_id IN (%s)', implode(',', $customAttributeIds)))
        );
        foreach ($customEntityAttributes as $record) {
            $record['attribute_group_id'] = $this->mapAttributeGroupIdsSourceDest[$record['attribute_group_id']];
            $record['sort_order'] = 130;
            $record['entity_attribute_id'] = null;
            $destinationRecord = $this->factory->create(['document' => $destinationDocument, 'data' => $record]);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->saveRecords($destinationDocument, $recordsToSave);
    }

    public function getCustomAttributeIds()
    {
        $defaultAttributes = [];
        foreach ($this->initialData->getAttributes('dest') as $id => $attribute) {
            $defaultAttributes[$id] = $attribute['attribute_code'] . '-' . $attribute['entity_type_id'];
        }
        $sourceAttributes = $this->ignoredAttributes->clearIgnoredAttributes(
            $this->initialData->getAttributes('source')
        );
        foreach ($sourceAttributes as $id => $attribute) {
            $sourceAttributes[$id] = $attribute['attribute_code'] . '-' . $attribute['entity_type_id'];
        }
        return array_keys(array_diff($sourceAttributes, $defaultAttributes));
    }

    /**
     * Move some fields to other attribute groups
     *
     * @param Record\Collection $recordsToSave
     * @return Record\Collection
     */
    private function moveProductAttributes($recordsToSave)
    {
        $this->moveProductAttributeToGroup($recordsToSave, 'price', 'product-details');
        $this->moveProductAttributeToGroup($recordsToSave, 'shipment_type', 'bundle-items');
        foreach ($this->attributesGroupToAdd as $attributeCode => $attributeGroupCode) {
            $this->addProductAttributeToGroup($recordsToSave, $attributeCode, $attributeGroupCode);
        }
        return $recordsToSave;
    }

    /**
     * Move attribute to other attribute group
     *
     * @param Record\Collection $recordsToSave
     * @param string $attributeCode
     * @param string $attributeGroupCode
     * @return Record\Collection
     */
    private function moveProductAttributeToGroup($recordsToSave, $attributeCode, $attributeGroupCode)
    {
        $productEntityType
            = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_code'])['catalog_product'];
        $attributes = $this->helper->getDestinationRecords('eav_attribute', ['attribute_id']);
        $attributeGroups = $this->helper->getDestinationRecords('eav_attribute_group', ['attribute_group_id']);
        $attributeSetGroups = [];
        foreach ($attributeGroups as $attributeGroup) {
            if ($attributeGroup['attribute_group_code'] == $attributeGroupCode) {
                $attributeSetGroups[$attributeGroup['attribute_set_id']][$attributeGroupCode] =
                    $attributeGroup['attribute_group_id'];
            }
        }
        foreach ($recordsToSave as $record) {
            $attributeId = $record->getValue('attribute_id');
            $entityTypeId = $record->getValue('entity_type_id');
            $attributeSetId = $record->getValue('attribute_set_id');
            if (!isset($attributes[$attributeId])
                || $entityTypeId != $productEntityType['entity_type_id']
                || $attributeCode != $attributes[$attributeId]['attribute_code']
                || !array_key_exists($attributeSetId, $attributeSetGroups)
            ) {
                continue;
            }
            $record->setValue('attribute_group_id', $attributeSetGroups[$attributeSetId][$attributeGroupCode]);
        }
        return $recordsToSave;
    }

    /**
     * Add attribute to attribute group
     *
     * @param Record\Collection $recordsToSave
     * @param string $attributeCode
     * @param string $attributeGroupCode
     * @return Record\Collection
     */
    private function addProductAttributeToGroup($recordsToSave, $attributeCode, $attributeGroupCode)
    {
        $productEntityType
            = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_code'])['catalog_product'];
        $productEntityTypeId = $productEntityType['entity_type_id'];
        $attributes = $this->helper->getDestinationRecords('eav_attribute', ['attribute_id']);
        $attributeGroups = $this->helper->getDestinationRecords('eav_attribute_group', ['attribute_group_id']);
        $attributeSets = $this->helper->getDestinationRecords('eav_attribute_set', ['attribute_set_id']);
        $attributeSetGroupsFound = [];
        $attribute = null;
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap('eav_entity_attribute', MapInterface::TYPE_SOURCE)
        );
        foreach ($recordsToSave as $record) {
            $attributeId = $record->getValue('attribute_id');
            $entityTypeId = $record->getValue('entity_type_id');
            if (isset($attributes[$attributeId])
                && $attributes[$attributeId]['attribute_code'] == $attributeCode
                && $entityTypeId == $productEntityTypeId
            ) {
                $attributeSetGroupsFound[$record->getValue('attribute_set_id')] =
                    $record->getValue('attribute_group_id');
                $attribute = $record->getData();
            }
        }
        if ($attribute === null) {
            return $recordsToSave;
        }
        foreach ($attributeGroups as $attributeGroup) {
            if ($attributeGroup['attribute_group_code'] == $attributeGroupCode
                && array_key_exists($attributeGroup['attribute_set_id'], $attributeSets)
                && $attributeSets[$attributeGroup['attribute_set_id']]['entity_type_id'] == $productEntityTypeId
                && !isset($attributeSetGroupsFound[$attributeGroup['attribute_set_id']])
            ) {
                $attribute['attribute_set_id'] = $attributeGroup['attribute_set_id'];
                $attribute['attribute_group_id'] = $attributeGroup['attribute_group_id'];
                $attribute['entity_attribute_id'] = null;
                $destinationRecord = $this->factory->create(
                    [
                        'document' => $destinationDocument,
                        'data' => $attribute
                    ]
                );
                $recordsToSave->addRecord($destinationRecord);
            }
        }
        return $recordsToSave;
    }

    /**
     * Move design attributes to schedule-design-update attribute groups
     *
     * @param Record\Collection $recordsToSave
     * @return Record\Collection
     * @throws \Migration\Exception
     */
    private function processDesignEntityAttributes($recordsToSave)
    {
        $data = $this->helper->getDesignAttributeAndGroupsData();
        $entityAttributeDocument = $this->destination->getDocument(
            $this->map->getDocumentMap('eav_entity_attribute', MapInterface::TYPE_SOURCE)
        );
        $recordsToSaveFiltered = $entityAttributeDocument->getRecords();
        foreach ($recordsToSave as $record) {
            /** @var Record $record */
            if (in_array($record->getValue('attribute_set_id'), $data['catalogProductSetIdsMigrated']) &&
                in_array($record->getValue('attribute_id'), [
                    $data['customDesignAttributeId'],
                    $data['customLayoutAttributeId']
                ])
            ) {
                continue;
            }
            $recordsToSaveFiltered->addRecord($record);
        }
        $recordsToSave = $recordsToSaveFiltered;

        foreach ($data['scheduleGroupsMigrated'] as $group) {
            if (isset($data['customDesignAttributeId']) && $data['customDesignAttributeId']) {
                $dataRecord = [
                    'entity_attribute_id' => null,
                    'entity_type_id' => $data['entityTypeIdCatalogProduct'],
                    'attribute_set_id' => $group['attribute_set_id'],
                    'attribute_group_id' => $group['attribute_group_id'],
                    'attribute_id' => $data['customDesignAttributeId'],
                    'sort_order' => 40,
                ];
                $destinationRecord = $this->factory->create([
                    'document' => $entityAttributeDocument,
                    'data' => $dataRecord
                ]);
                /** Adding custom_design */
                $recordsToSave->addRecord($destinationRecord);
            }

            if (isset($data['customLayoutAttributeId']) && $data['customLayoutAttributeId']) {
                $dataRecord = [
                    'entity_attribute_id' => null,
                    'entity_type_id' => $data['entityTypeIdCatalogProduct'],
                    'attribute_set_id' => $group['attribute_set_id'],
                    'attribute_group_id' => $group['attribute_group_id'],
                    'attribute_id' => $data['customLayoutAttributeId'],
                    'sort_order' => 50,
                ];
                $destinationRecord = $this->factory->create([
                    'document' => $entityAttributeDocument,
                    'data' => $dataRecord
                ]);
                /** Adding custom_layout */
                $recordsToSave->addRecord($destinationRecord);
            }
        }

        return $recordsToSave;
    }

    /**
     * There are attributes from destination customer attribute set
     * that do not exit in source customer attribute set.
     * The method adds the lacking attributes
     *
     * @param Record\Collection $recordsToSave
     * @return Record\Collection
     */
    private function addLackingAttributesToCustomerAttributeSet($recordsToSave)
    {
        $entityAttributeDocument = $this->destination->getDocument(
            $this->map->getDocumentMap('eav_entity_attribute', MapInterface::TYPE_SOURCE)
        );
        $customerEntityType
            = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_code'])['customer'];
        $customerEntityTypeId = $customerEntityType['entity_type_id'];
        $attributeGroups = $this->helper->getDestinationRecords('eav_attribute_group', ['attribute_group_id']);
        $attributeSets = $this->helper->getDestinationRecords('eav_attribute_set', ['attribute_set_id']);
        $attributeSetNameCustomerSource = 'Migration_Default';
        $attributeSetNameCustomerDestination = 'Default';
        $attributeSetIdOfCustomerSource = null;
        $attributeSetIdOfCustomerDestination = null;
        $eavEntityAttributeOfCustomerSource = [];
        $eavEntityAttributeOfCustomerDestination = [];
        $attributeGroupIfOfCustomerSource = null;

        foreach ($attributeSets as $attributeSet) {
            if ($attributeSet['entity_type_id'] == $customerEntityTypeId
                && $attributeSet['attribute_set_name'] == $attributeSetNameCustomerSource
            ) {
                $attributeSetIdOfCustomerSource = $attributeSet['attribute_set_id'];
            } elseif ($attributeSet['entity_type_id'] == $customerEntityTypeId
                && $attributeSet['attribute_set_name'] == $attributeSetNameCustomerDestination
            ) {
                $attributeSetIdOfCustomerDestination = $attributeSet['attribute_set_id'];
            }
        }
        foreach ($attributeGroups as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $attributeSetIdOfCustomerSource) {
                $attributeGroupIfOfCustomerSource = $attributeGroup['attribute_group_id'];
            }
        }
        if ($attributeSetIdOfCustomerSource === null
            || $attributeSetIdOfCustomerDestination === null
            || $attributeGroupIfOfCustomerSource === null
        ) {
            return $recordsToSave;
        }

        foreach ($recordsToSave as $record) {
            $attributeId = $record->getValue('attribute_id');
            $attributeSetId = $record->getValue('attribute_set_id');
            if ($attributeSetId == $attributeSetIdOfCustomerSource) {
                $eavEntityAttributeOfCustomerSource[] = $attributeId;
            } else if ($attributeSetId == $attributeSetIdOfCustomerDestination) {
                $eavEntityAttributeOfCustomerDestination[] = $attributeId;
            }
        }
        $customerAttributeIdsToAdd = array_diff(
            $eavEntityAttributeOfCustomerDestination,
            $eavEntityAttributeOfCustomerSource
        );
        foreach ($customerAttributeIdsToAdd as $customerAttributeId) {
            $dataRecord = [
                'entity_attribute_id' => null,
                'entity_type_id' => $customerEntityTypeId,
                'attribute_set_id' => $attributeSetIdOfCustomerSource,
                'attribute_group_id' => $attributeGroupIfOfCustomerSource,
                'attribute_id' => $customerAttributeId,
                'sort_order' => 50,
            ];
            $destinationRecord = $this->factory->create([
                'document' => $entityAttributeDocument,
                'data' => $dataRecord
            ]);
            $recordsToSave->addRecord($destinationRecord);
        }

        return $recordsToSave;
    }

    /**
     * Migrate tables extended from eav_attribute
     *
     * @return void
     */
    protected function migrateAttributesExtended()
    {
        $documents = $this->readerGroups->getGroup('documents_attribute_extended');
        foreach ($documents as $documentName => $mappingField) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($documentName);
            $destinationDocument = $this->destination->getDocument(
                $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
            );
            $this->destination->backupDocument($destinationDocument->getName());
            $destinationRecords = $this->helper->getDestinationRecords($documentName, [$mappingField]);
            $recordsToSave = $destinationDocument->getRecords();
            $sourceRecords = $this->ignoredAttributes
                ->clearIgnoredAttributes($this->helper->getSourceRecords($documentName));
            $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
            foreach ($sourceRecords as $sourceRecordData) {
                /** @var Record $sourceRecord */
                $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $sourceRecordData]);
                /** @var Record $destinationRecord */
                $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
                $mappedId = isset($this->mapAttributeIdsSourceDest[$sourceRecord->getValue($mappingField)])
                    ? $this->mapAttributeIdsSourceDest[$sourceRecord->getValue($mappingField)]
                    : null;
                if ($mappedId !== null && isset($destinationRecords[$mappedId])) {
                    $destinationRecordData = $destinationRecords[$mappedId];
                    $destinationRecordData['attribute_id'] = $sourceRecordData['attribute_id'];
                    $destinationRecord->setData($destinationRecordData);
                    unset($destinationRecords[$mappedId]);
                } else {
                    $destinationRecordData = $destinationRecord->getDataDefault();
                    $destinationRecord->setData($destinationRecordData);
                    $recordTransformer->transform($sourceRecord, $destinationRecord);
                }
                $recordsToSave->addRecord($destinationRecord);
            }
            $this->destination->clearDocument($destinationDocument->getName());
            $this->saveRecords($destinationDocument, $recordsToSave);

            $recordsToSave = $destinationDocument->getRecords();
            foreach ($destinationRecords as $record) {
                $record['attribute_id'] = $this->mapAttributeIdsDestOldNew[$record['attribute_id']];
                $destinationRecord = $this->factory->create([
                    'document' => $destinationDocument,
                    'data' => $record
                ]);
                $recordsToSave->addRecord($destinationRecord);
            }
            $this->saveRecords($destinationDocument, $recordsToSave);
        }
    }

    /**
     * Save records
     *
     * @param Document|string $document
     * @param Record\Collection|array $recordsToSave
     * @return void
     */
    protected function saveRecords($document, $recordsToSave)
    {
        if (is_object($document)) {
            $document = $document->getName();
        }
        $this->destination->saveRecords($document, $recordsToSave);
    }

    /**
     * Create mapping for entity type ids
     *
     * @return void
     */
    protected function createMapEntityTypeIds()
    {
        $entityTypesMigrated = $this->helper->getDestinationRecords(
            'eav_entity_type',
            ['entity_type_code']
        );
        foreach ($this->initialData->getEntityTypes('dest') as $entityTypeIdOld => $recordOld) {
            $entityTypeMigrated = $entityTypesMigrated[$recordOld['entity_type_code']];
            $this->mapEntityTypeIdsDestOldNew[$entityTypeIdOld] = $entityTypeMigrated['entity_type_id'];
        }
        foreach ($this->initialData->getEntityTypes('source') as $entityTypeIdSource => $recordSource) {
            foreach ($this->initialData->getEntityTypes('dest') as $entityTypeIdDest => $recordDest) {
                if ($recordSource['entity_type_code'] == $recordDest['entity_type_code']) {
                    $this->mapEntityTypeIdsSourceDest[$entityTypeIdSource] = $entityTypeIdDest;
                }
            }
        }
    }

    /**
     * Create mapping for attribute set ids
     *
     * @return void
     */
    protected function createMapAttributeSetIds()
    {
        $this->newAttributeSets = $this->helper->getDestinationRecords(
            'eav_attribute_set',
            ['entity_type_id', 'attribute_set_name']
        );
        foreach ($this->initialData->getAttributeSets('dest') as $attributeSetId => $record) {
            $entityTypeId = $this->mapEntityTypeIdsDestOldNew[$record['entity_type_id']];
            $newAttributeSet = $this->newAttributeSets[$entityTypeId . '-' . $record['attribute_set_name']];
            $this->mapAttributeSetIdsDestOldNew[$attributeSetId] = $newAttributeSet['attribute_set_id'];
            $this->defaultAttributeSetIds[$newAttributeSet['entity_type_id']] = $newAttributeSet['attribute_set_id'];
        }
    }

    /**
     * Create mapping for attribute ids
     *
     * @return void
     */
    protected function createMapAttributeIds()
    {
        $newAttributes = $this->helper->getDestinationRecords(
            'eav_attribute',
            ['entity_type_id', 'attribute_code']
        );
        foreach ($this->initialData->getAttributes('dest') as $keyOld => $attributeOld) {
            $entityTypeId = $attributeOld['entity_type_id'];
            $attributeCode = $attributeOld['attribute_code'];
            $keyMapped = $this->mapEntityTypeIdsDestOldNew[$entityTypeId] . '-' . $attributeCode;
            $this->mapAttributeIdsDestOldNew[$attributeOld['attribute_id']] =
                $newAttributes[$keyMapped]['attribute_id'];
        }
        foreach ($this->initialData->getAttributes('source') as $recordSourceId => $recordSource) {
            foreach ($this->initialData->getAttributes('dest') as $recordDestId => $recordDest) {
                $sourceEntityTypeCode = $this->initialData->getEntityTypes('source')
                [$recordSource['entity_type_id']]['entity_type_code'];
                $destinationEntityTypeCode = $this->initialData->getEntityTypes('dest')
                [$recordDest['entity_type_id']]['entity_type_code'];
                if ($recordSource['attribute_code'] == $recordDest['attribute_code']
                    && $sourceEntityTypeCode == $destinationEntityTypeCode
                ) {
                    $this->mapAttributeIdsSourceDest[$recordSourceId] = $recordDestId;
                }
            }
        }
    }

    /**
     * Create mapping for product attribute group ids
     */
    protected function createMapProductAttributeGroupIds()
    {
        $attributeGroupsDestination = $this->helper->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_group_id']
        );
        $attributeGroupsSource = $this->helper->getSourceRecords(
            'eav_attribute_group',
            ['attribute_group_id']
        );
        $productAttributeSetIds = array_keys($this->getAttributeSets(
            $this->getEntityTypeIdByCode(self::ENTITY_TYPE_PRODUCT_CODE),
            self::ATTRIBUTE_SETS_ALL
        ));
        foreach ($attributeGroupsSource as $idSource => $recordSource) {
            $sourceAttributeGroupName = $recordSource['attribute_group_name'];
            if (in_array($recordSource['attribute_set_id'], $productAttributeSetIds)) {
                $sourceAttributeGroupName = str_replace(
                    array_keys($this->mapProductAttributeGroupNamesSourceDest),
                    $this->mapProductAttributeGroupNamesSourceDest,
                    $recordSource['attribute_group_name']
                );
            }
            $sourceKey = $recordSource['attribute_set_id'] . ' ' . $sourceAttributeGroupName;
            foreach ($attributeGroupsDestination as $idDestination => $recordDestination) {
                $destinationKey = $recordDestination['attribute_set_id']
                    . ' '
                    . $recordDestination['attribute_group_name'];
                if ($sourceKey == $destinationKey) {
                    $this->mapAttributeGroupIdsSourceDest[$recordSource['attribute_group_id']] =
                        $recordDestination['attribute_group_id'];
                }
            }
        }
    }

    /**
     * Get iterations count
     *
     * @return int
     */
    public function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('documents'));
    }

    /**
     * Rollback backed up documents
     *
     * @return void
     */
    public function rollback()
    {
        foreach (array_keys($this->readerGroups->getGroup('documents')) as $documentName) {
            $destinationDocument = $this->destination->getDocument(
                $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
            );
            if ($destinationDocument !== false) {
                $this->destination->rollbackDocument($destinationDocument->getName());
            }
        }
    }
}
