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
use Migration\Step\Eav\Model\Data as ModelData;

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
    private $newAttributeSets = [];

    /**
     * @var array;
     */
    private $mapAttributeIdsDestOldNew = [];

    /**
     * @var array;
     */
    private $mapAttributeIdsSourceDest = [];

    /**
     * @var array;
     */
    private $mapAttributeSetIdsDestOldNew = [];

    /**
     * @var array;
     */
    private $mapEntityTypeIdsDestOldNew = [];

    /**
     * @var array;
     */
    private $mapEntityTypeIdsSourceDest = [];

    /**
     * @var array;
     */
    private $defaultAttributeSetIds = [];

    /**
     * @var array;
     */
    private $mapAttributeGroupIdsSourceDest = [];

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var Destination
     */
    private $destination;

    /**
     * @var Map
     */
    private $map;

    /**
     * @var RecordFactory
     */
    private $factory;

    /**
     * @var InitialData
     */
    private $initialData;

    /**
     * @var IgnoredAttributes
     */
    private $ignoredAttributes;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var ModelData
     */
    private $modelData;

    /**
     * @var array
     */
    private $mapProductAttributeGroupNamesSourceDest = [
        'General' => 'Product Details',
        'Prices' => 'Product Details',
        'Recurring Profile' => 'Product Details'
    ];

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
     * @param ModelData $modelData
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
        ProgressBar\LogLevelProcessor $progress,
        ModelData $modelData
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
        $this->modelData = $modelData;
    }

    /**
     * Entry point. Run migration of EAV structure.
     *
     * @return bool
     */
    public function perform()
    {
        $this->progress->start(7);
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
    private function migrateEntityTypes()
    {
        $this->progress->advance();
        $documentName = 'eav_entity_type';
        $mappingField = 'entity_type_code';
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
     * Migrate attribute set table
     *
     * @return void
     */
    private function migrateAttributeSets()
    {
        $this->progress->advance();
        $documentName = 'eav_attribute_set';
        $sourceDocument = $this->source->getDocument($documentName);
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($documentName, MapInterface::TYPE_SOURCE)
        );
        $this->destination->backupDocument($destinationDocument->getName());
        $destinationRecords = $this->helper->getDestinationRecords(
            $documentName,
            ['entity_type_id', 'attribute_set_name']
        );
        $sourceRecords = $this->source->getRecords($documentName, 0, $this->source->getRecordsCount($documentName));
        $recordsToSave = $destinationDocument->getRecords();
        $recordTransformer = $this->helper->getRecordTransformer($sourceDocument, $destinationDocument);
        foreach ($sourceRecords as $recordData) {
            $sourceRecord = $this->factory->create(['document' => $sourceDocument, 'data' => $recordData]);
            $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
            $mappedKey = null;
            $entityTypeId = $sourceRecord->getValue('entity_type_id');
            if (isset($this->mapEntityTypeIdsSourceDest[$entityTypeId])) {
                $mappedId = $this->mapEntityTypeIdsSourceDest[$entityTypeId];
                $mappedKey = $mappedId . '-' . $sourceRecord->getValue('attribute_set_name');
            }
            if ($mappedKey && isset($destinationRecords[$mappedKey])) {
                unset($destinationRecords[$mappedKey]);
            }
            $destinationRecordData = $destinationRecord->getDataDefault();
            $destinationRecord->setData($destinationRecordData);
            $recordTransformer->transform($sourceRecord, $destinationRecord);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->destination->clearDocument($destinationDocument->getName());
        $this->saveRecords($destinationDocument, $recordsToSave);

        $recordsToSave = $destinationDocument->getRecords();
        foreach ($destinationRecords as $recordData) {
            /** @var Record $destinationRecord */
            $destinationRecord = $this->factory->create(['document' => $destinationDocument, 'data' => $recordData]);
            $destinationRecord->setValue('attribute_set_id', null);
            $destinationRecord->setValue(
                'entity_type_id',
                $this->mapEntityTypeIdsDestOldNew[$destinationRecord->getValue('entity_type_id')]
            );
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->saveRecords($destinationDocument, $recordsToSave);
        $this->createMapAttributeSetIds();
    }

    /**
     * Take Default attribute set structure and duplicate it for all  attribute sets from Magento 1
     */
    private function createProductAttributeSetStructures()
    {
        $this->progress->advance();
        $documentName = 'eav_attribute_group';
        $this->destination->backupDocument($documentName);
        $this->modelData->updateMappedKeys(
            $documentName,
            'attribute_set_id',
            $this->helper->getDestinationRecords($documentName),
            $this->mapAttributeSetIdsDestOldNew
        );
        // add default attribute groups from Magento 2 for each attribute set from Magento 1
        $prototypeProductAttributeGroups = $this->modelData->getDefaultProductAttributeGroups();
        $productAttributeSets = $this->modelData->getAttributeSets(
            $this->modelData->getEntityTypeIdByCode(ModelData::ENTITY_TYPE_PRODUCT_CODE),
            ModelData::ATTRIBUTE_SETS_NONE_DEFAULT
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
        $this->modelData->updateMappedKeys(
            $entityAttributeDocument,
            'attribute_set_id',
            $this->helper->getDestinationRecords($entityAttributeDocument),
            $this->mapAttributeSetIdsDestOldNew
        );
        // add default entity attributes from Magento 2 for each attribute set from Magento 1
        foreach ($productAttributeSets as $attributeSet) {
            $prototypeProductEntityAttributes = $this->modelData->getDefaultProductEntityAttributes();
            foreach ($prototypeProductEntityAttributes as &$prototypeEntityAttribute) {
                $attributeGroupId = $this->modelData->getAttributeGroupIdForAttributeSet(
                    $prototypeEntityAttribute['attribute_group_id'],
                    $attributeSet['attribute_set_id']
                );
                $prototypeEntityAttribute['attribute_set_id'] = $attributeSet['attribute_set_id'];
                $prototypeEntityAttribute['attribute_group_id'] = $attributeGroupId;
            }
            $this->saveRecords($entityAttributeDocument, $prototypeProductEntityAttributes);
        }
    }

    /**
     * Migrate custom product attribute groups
     */
    public function migrateCustomProductAttributeGroups()
    {
        $this->progress->advance();
        $productAttributeSets = $this->modelData->getAttributeSets(
            $this->modelData->getEntityTypeIdByCode(ModelData::ENTITY_TYPE_PRODUCT_CODE),
            ModelData::ATTRIBUTE_SETS_ALL
        );
        foreach ($productAttributeSets as $productAttributeSet) {
            $attributeGroupIds = $this->modelData->getCustomProductAttributeGroups(
                $productAttributeSet['attribute_set_id']
            );
            if ($attributeGroupIds) {
                $this->migrateAttributeGroups($attributeGroupIds);
            }
        }
        $this->createMapProductAttributeGroupIds();
    }

    /**
     * Migrate attribute groups
     *
     * @param $attributeGroupIds
     */
    private function migrateAttributeGroups($attributeGroupIds)
    {
        $this->progress->advance();
        $documentName = 'eav_attribute_group';
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
     * Migrate eav_attribute
     *
     * @return void
     */
    private function migrateAttributes()
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
        $this->modelData->updateMappedKeys(
            'eav_entity_attribute',
            'attribute_id',
            $this->helper->getDestinationRecords('eav_entity_attribute'),
            $mapAttributeIds
        );
    }

    /**
     * Migrate custom entity attributes
     */
    private function migrateCustomEntityAttributes()
    {
        $this->progress->advance();
        $sourceDocName = 'eav_entity_attribute';
        $destinationDocument = $this->destination->getDocument(
            $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE)
        );
        $recordsToSave = $destinationDocument->getRecords();
        $customAttributeIds = $this->modelData->getCustomAttributeIds();
        $customEntityAttributes = $this->source->getRecords(
            $sourceDocName,
            0,
            $this->source->getRecordsCount($sourceDocName),
            new \Zend_Db_Expr(sprintf('attribute_id IN (%s)', implode(',', $customAttributeIds)))
        );
        foreach ($customEntityAttributes as $record) {
            $record['sort_order'] = $this->getCustomAttributeSortOrder($record);
            $record['attribute_group_id'] = $this->mapAttributeGroupIdsSourceDest[$record['attribute_group_id']];
            $record['entity_attribute_id'] = null;
            $destinationRecord = $this->factory->create(['document' => $destinationDocument, 'data' => $record]);
            $recordsToSave->addRecord($destinationRecord);
        }
        $this->saveRecords($destinationDocument, $recordsToSave);
    }

    /**
     * Get sort order for custom attribute
     *
     * @param array $attribute
     * @return int
     */
    private function getCustomAttributeSortOrder(array $attribute)
    {
        $productEntityTypeId = $this->modelData->getEntityTypeIdByCode(ModelData::ENTITY_TYPE_PRODUCT_CODE);
        $groupName = $this->modelData->getSourceAttributeGroupNameFromId($attribute['attribute_group_id']);
        if ($attribute['entity_type_id'] == $productEntityTypeId
            && isset($this->mapProductAttributeGroupNamesSourceDest[$groupName])
        ) {
            return $attribute['sort_order'] + 200;
        }
        return $attribute['sort_order'];
    }

    /**
     * Migrate tables extended from eav_attribute
     */
    private function migrateAttributesExtended()
    {
        $this->progress->advance();
        $documents = $this->readerGroups->getGroup('documents_attribute_extended');
        foreach ($documents as $documentName => $mappingField) {
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
    private function saveRecords($document, $recordsToSave)
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
    private function createMapEntityTypeIds()
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
    private function createMapAttributeSetIds()
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
    private function createMapAttributeIds()
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
    private function createMapProductAttributeGroupIds()
    {
        $attributeGroupsDestination = $this->helper->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_group_id']
        );
        $attributeGroupsSource = $this->helper->getSourceRecords(
            'eav_attribute_group',
            ['attribute_group_id']
        );
        $productAttributeSetIds = array_keys($this->modelData->getAttributeSets(
            $this->modelData->getEntityTypeIdByCode(ModelData::ENTITY_TYPE_PRODUCT_CODE),
            ModelData::ATTRIBUTE_SETS_ALL
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
