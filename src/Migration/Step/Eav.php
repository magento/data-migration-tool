<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Config;
use Migration\RecordTransformer;
use Migration\Resource\Destination;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\Resource\RecordFactory;
use Migration\Resource\Source;
use Migration\Resource\Structure;

/**
 * Class Eav
 */
class Eav extends AbstractStep
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $dest;

    /**
     * @var MapReader
     */
    protected $map;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Migration\RecordTransformer
     */
    protected $recordTransformerFactory;

    /**
     * @var RecordFactory
     */
    protected $recordFactory;

    /**
     * @var array
     */
    protected $initialAttributes;

    /**
     * @var array;
     */
    protected $initialAttributeSets;

    /**
     * @var array;
     */
    protected $initialAttributeGroups;

    /**
     * @var array;
     */
    protected $newAttributes;

    /**
     * @var array;
     */
    protected $newAttributeSets;

    /**
     * @var array;
     */
    protected $newAttributeGroups;

    /**
     * @var array;
     */
    protected $destAttributeOldNewMap;

    /**
     * @var array;
     */
    protected $destAttributeSetsOldNewMap;

    /**
     * @var array;
     */
    protected $destAttributeGroupsOldNewMap;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param Source $source
     * @param Destination $dest
     * @param \Migration\RecordTransformerFactory $recordTransformer
     * @param MapReader $map
     * @param Config $config
     * @param RecordFactory $recordFactory
     * @throws \Exception
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        Source $source,
        Destination $dest,
        \Migration\RecordTransformerFactory $recordTransformer,
        MapReader $map,
        Config $config,
        RecordFactory $recordFactory
    ) {
        parent::__construct($progress, $logger);
        $this->source = $source;
        $this->dest = $dest;
        $this->config = $config;
        $this->map = $map->init($this->config->getOption('eav_map_file'));
        $this->recordTransformerFactory = $recordTransformer;
        $this->recordFactory = $recordFactory;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function integrity()
    {
        $missingDocuments = [];
        $missingFields = [];
        $sourceDocuments = array_keys($this->getDocumentsList());
        $destDocuments = array_flip($this->dest->getDocumentList());
        $destEavDocuments = [];
        foreach ($sourceDocuments as $document) {
            $mappedDocument = $this->map->getDocumentMap($document, MapReader::TYPE_SOURCE);
            if ($mappedDocument !== false) {
                $destEavDocuments[] = $mappedDocument;
                if (!isset($destDocuments[$mappedDocument])) {
                    $missingDocuments[MapReader::TYPE_SOURCE][$document] = true;
                } else {
                    $fields = array_keys($this->source->getDocument($document)->getStructure()->getFields());
                    $destFields = $this->dest->getDocument($mappedDocument)->getStructure()->getFields();
                    foreach ($fields as $field) {
                        $mappedField = $this->map->getFieldMap($document, $field, MapReader::TYPE_SOURCE);
                        if ($mappedField && !isset($destFields[$mappedField])) {
                            $missingFields[MapReader::TYPE_SOURCE][$document][] = $mappedField;
                        }
                    }
                }
            }
        }
        $sourceDocuments = array_flip($sourceDocuments);
        foreach ($destEavDocuments as $document) {
            $mappedDocument = $this->map->getDocumentMap($document, MapReader::TYPE_DEST);
            if ($mappedDocument !== false) {
                if (!isset($sourceDocuments[$mappedDocument])) {
                    $missingDocuments[MapReader::TYPE_DEST][$document] = true;
                } else {
                    $fields = array_keys($this->dest->getDocument($document)->getStructure()->getFields());
                    $destFields = $this->source->getDocument($mappedDocument)->getStructure()->getFields();
                    foreach ($fields as $field) {
                        $mappedField = $this->map->getFieldMap($document, $field, MapReader::TYPE_DEST);
                        if ($mappedField && !isset($destFields[$mappedField])) {
                            $missingFields[MapReader::TYPE_DEST][$document][] = $mappedField;
                        }
                    }
                }
            }
        }

        if (!empty($missingFields) || !empty($missingDocuments)) {
            var_dump($missingFields);
            var_dump($missingDocuments);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function run()
    {
        parent::run();
        if (!$this->integrity()) {
            return;
        }
        $this->progress->start(count($this->getDocumentsList()));
        $this->loadInitialAttributeSets();
        $this->loadInitialAttributeGroups();
        $this->loadInitialAttributes();

        $this->migrateAttributeSetsAndGroups();
        $this->migrateAttributes();
        $this->migrateEntityAttributes();
        $this->migrateOtherTables();
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function migrateAttributeSetsAndGroups()
    {
        foreach (['eav_attribute_set', 'eav_attribute_group'] as $documentName) {
            $sourceDocument = $this->source->getDocument($documentName);
            $destDocument = $this->dest->getDocument(
                $this->map->getDocumentMap($documentName, MapReader::TYPE_SOURCE)
            );

            $sourceRecords = $this->source->getRecords($documentName, 0, $this->source->getRecordsCount($documentName));
            $recordsToSave = $destDocument->getRecords();
            foreach ($sourceRecords as $recordData) {
                $sourceRecord = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                $this->getRecordTransformer($sourceDocument, $destDocument)->transform($sourceRecord, $destRecord);
                $recordsToSave->addRecord($destRecord);
            }

            if ($documentName == 'eav_attribute_set') {
                foreach ($this->initialAttributeSets['dest'] as $record) {
                    $record['attribute_set_id'] = null;
                    $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $record]);
                    $recordsToSave->addRecord($destRecord);
                }
            }

            if ($documentName == 'eav_attribute_group') {
                foreach ($this->initialAttributeGroups['dest'] as $record) {
                    $oldAttributeSet = $this->initialAttributeSets['dest'][$record['attribute_set_id']];
                    $newAttributeSet = $this->newAttributeSets[
                        $oldAttributeSet['entity_type_id'] . '-' . $oldAttributeSet['attribute_set_name']
                    ];
                    $record['attribute_set_id'] = $newAttributeSet['attribute_set_id'];

                    $record['attribute_group_id'] = null;
                    $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $record]);
                    $recordsToSave->addRecord($destRecord);
                }
            }

            $this->saveRecords($destDocument, $recordsToSave);
            if ($documentName == 'eav_attribute_set') {
                $this->loadNewAttributeSets();
            }
            if ($documentName == 'eav_attribute_group') {
                $this->loadNewAttributeGroups();
            }
        }
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function migrateAttributes()
    {
        $sourceDocName = 'eav_attribute';
        $sourceDocument = $this->source->getDocument($sourceDocName);
        $destDocument = $this->dest->getDocument(
            $this->map->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE)
        );

        $sourceRecords = $this->source->getRecords($sourceDocName, 0, $this->source->getRecordsCount($sourceDocName));
        $destinationRecords = $this->initialAttributes['dest'];

        $recordsToSave = $destDocument->getRecords();
        foreach ($sourceRecords as $sourceRecordData) {
            $sourceRecord = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $sourceRecordData]);
            $destRecord = $this->recordFactory->create(['document' => $destDocument]);

            $mappingValue = $this->getMappingValue($sourceRecord, ['entity_type_id', 'attribute_code']);
            if (isset($destinationRecords[$mappingValue])) {
                $destRecordData = $destinationRecords[$mappingValue];
                unset($destinationRecords[$mappingValue]);
            } else {
                $destRecordData = array_fill_keys($destRecord->getFields(), null);
            }
            $destRecord->setData($destRecordData);

            $this->getRecordTransformer($sourceDocument, $destDocument)->transform($sourceRecord, $destRecord);
            $recordsToSave->addRecord($destRecord);
        }

        foreach ($destinationRecords as $record) {
            $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $record]);
            $destRecord->setValue('attribute_id', null);
            $recordsToSave->addRecord($destRecord);
        }

        $this->saveRecords($destDocument, $recordsToSave);
        $this->loadNewAttributes();
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function migrateEntityAttributes()
    {
        $sourceDocName = 'eav_entity_attribute';
        $sourceDocument = $this->source->getDocument($sourceDocName);
        $destDocument = $this->dest->getDocument(
            $this->map->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE)
        );

        $sourceRecords = $this->source->getRecords($sourceDocName, 0, $this->source->getRecordsCount($sourceDocName));
        $recordsToSave = $destDocument->getRecords();
        foreach ($sourceRecords as $sourceRecordData) {
            $sourceRecord = $this->recordFactory->create(
                [
                    'document' => $sourceDocument,
                    'data' => $sourceRecordData
                ]
            );
            $destRecord = $this->recordFactory->create(['document' => $destDocument]);
            $this->getRecordTransformer($sourceDocument, $destDocument)->transform($sourceRecord, $destRecord);
            $recordsToSave->addRecord($destRecord);
        }

        foreach ($this->getDestinationRecords('eav_entity_attribute') as $record) {
            if (!isset($this->destAttributeOldNewMap[$record['attribute_id']])
                || !isset($this->destAttributeSetsOldNewMap[$record['attribute_set_id']])
                || !isset($this->destAttributeGroupsOldNewMap[$record['attribute_group_id']])
            ) {
                continue;
            }
            $record['attribute_id'] = $this->destAttributeOldNewMap[$record['attribute_id']];
            $record['attribute_set_id'] = $this->destAttributeSetsOldNewMap[$record['attribute_set_id']];
            $record['attribute_group_id'] = $this->destAttributeGroupsOldNewMap[$record['attribute_group_id']];

            $record['entity_attribute_id'] = null;
            $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $record]);
            $recordsToSave->addRecord($destRecord);
        }

        $this->saveRecords($destDocument, $recordsToSave);
    }

    /**
     * @throws \Exception
     * @return void
     */
    protected function migrateOtherTables()
    {
        $tables = array_diff_key($this->getDocumentsList(), array_flip([
            'eav_attribute_set',
            'eav_attribute_group',
            'eav_attribute',
            'eav_entity_attribute'
        ]));
        foreach ($tables as $documentName => $mappingFields) {
            $sourceDocument = $this->source->getDocument($documentName);
            $destDocument = $this->dest->getDocument(
                $this->map->getDocumentMap($documentName, MapReader::TYPE_SOURCE)
            );

            $sourceRecords = $this->source->getRecords($documentName, 0, $this->source->getRecordsCount($documentName));
            $destinationRecords = $this->getDestinationRecords($documentName, $mappingFields);

            $recordsToSave = $destDocument->getRecords();
            foreach ($sourceRecords as $recordData) {
                $sourceRecord = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                $destRecord = $this->recordFactory->create(['document' => $destDocument]);

                if ($mappingFields) {
                    $mappingValue = $this->getMappingValue($sourceRecord, $mappingFields);
                    if (isset($destinationRecords[$mappingValue])) {
                        $destRecordData = $destinationRecords[$mappingValue];
                        unset($destinationRecords[$mappingValue]);
                    } else {
                        $destRecordData = array_fill_keys($destRecord->getFields(), null);
                    }
                    $destRecord->setData($destRecordData);
                }

                $this->getRecordTransformer($sourceDocument, $destDocument)->transform($sourceRecord, $destRecord);
                $recordsToSave->addRecord($destRecord);
            }

            if ($mappingFields) {
                foreach ($destinationRecords as $record) {
                    $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $record]);
                    if (isset($record['attribute_id'])
                        && isset($this->destAttributeOldNewMap[$record['attribute_id']])
                    ) {
                        $destRecord->setValue('attribute_id', $this->destAttributeOldNewMap[$record['attribute_id']]);
                    }
                    $recordsToSave->addRecord($destRecord);
                }
            }

            $this->saveRecords($destDocument, $recordsToSave);
        }
    }

    /**
     * @param Document $document
     * @param Record\Collection $recordsToSave
     * @return void
     */
    protected function saveRecords(Document $document, Record\Collection $recordsToSave)
    {
        $this->dest->clearDocument($document->getName());
        $this->dest->saveRecords($document->getName(), $recordsToSave);
        $this->progress->advance();
    }

    /**
     * @param Document $sourceDocument
     * @param Document $destinationDocument
     * @return RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        return $this->recordTransformerFactory->create([
            'sourceDocument' => $sourceDocument,
            'destDocument' => $destinationDocument,
            'mapReader' => $this->map
        ])->init();
    }

    /**
     * @param string $sourceDocName
     * @param array $mapKey
     * @return array
     */
    protected function getDestinationRecords($sourceDocName, $mapKey = [])
    {
        $destDocumentName = $this->map->getDocumentMap($sourceDocName, MapReader::TYPE_SOURCE);
        $data = [];
        $count = $this->dest->getRecordsCount($destDocumentName);
        foreach ($this->dest->getRecords($destDocumentName, 0, $count) as $row) {
            if ($mapKey) {
                $key = [];
                foreach ($mapKey as $keyField) {
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
     * @param Record $sourceRecord
     * @param array $keyFields
     * @return string
     */
    protected function getMappingValue(Record $sourceRecord, $keyFields)
    {
        $value = [];
        foreach ($keyFields as $field) {
            switch ($field) {
                case 'attribute_id':
                    $value[] =  $this->getDestinationAttributeId($sourceRecord->getValue($field));
                    break;
                default:
                    $value[] = $sourceRecord->getValue($field);
                    break;
            }
        }
        return implode('-', $value);
    }

    /**
     * Load all attributes from source and destination
     * @return void
     */
    protected function loadInitialAttributes()
    {
        if (is_null($this->initialAttributes)) {
            $sourceDocument = 'eav_attribute';
            $destDocument = $this->map->getDocumentMap($sourceDocument, MapReader::TYPE_SOURCE);
            $records = $this->source->getRecords($sourceDocument, 0, $this->source->getRecordsCount($sourceDocument));
            foreach ($records as $record) {
                $this->initialAttributes['source'][$record['attribute_id']] = $record;
            }

            $records = $this->dest->getRecords($destDocument, 0, $this->dest->getRecordsCount($destDocument));
            foreach ($records as $record) {
                $this->initialAttributes['dest'][$record['entity_type_id'] . '-' . $record['attribute_code']] = $record;
            }
        }
    }

    /**
     * Load attribute sets data before migration
     * @return void
     */
    protected function loadInitialAttributeSets()
    {
        $this->initialAttributeSets['dest'] = $this->getDestinationRecords('eav_attribute_set', ['attribute_set_id']);
    }

    /**
     * Load attribute group data before migration
     * @return void
     */
    protected function loadInitialAttributeGroups()
    {
        $this->initialAttributeGroups['dest'] = $this->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_set_id', 'attribute_group_name']
        );
    }

    /**
     * Load migrated attribute sets data
     * @return void
     */
    protected function loadNewAttributeSets()
    {
        $this->newAttributeSets = $this->getDestinationRecords(
            'eav_attribute_set',
            ['entity_type_id', 'attribute_set_name']
        );
        foreach ($this->initialAttributeSets['dest'] as $attributeSetId => $record) {
            $newAttributeSet = $this->newAttributeSets[$record['entity_type_id'] . '-' . $record['attribute_set_name']];
            $this->destAttributeSetsOldNewMap[$attributeSetId] = $newAttributeSet['attribute_set_id'];
        }
    }

    /**
     * Load migrated attribute groups data
     * @return void
     */
    protected function loadNewAttributeGroups()
    {
        $this->newAttributeGroups = $this->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_set_id', 'attribute_group_name']
        );
        foreach ($this->initialAttributeGroups['dest'] as $record) {
            $newKey = $this->destAttributeSetsOldNewMap[$record['attribute_set_id']] . '-'
                . $record['attribute_group_name'];
            $newAttributeGroup = $this->newAttributeGroups[$newKey];
            $this->destAttributeGroupsOldNewMap[
                $record['attribute_group_id']] = $newAttributeGroup['attribute_group_id'
            ];
        }
    }

    /**
     * Load migrated attributes data
     * @return array
     */
    protected function loadNewAttributes()
    {
        $this->newAttributes = $this->getDestinationRecords('eav_attribute', ['entity_type_id', 'attribute_code']);
        foreach ($this->initialAttributes['dest'] as $key => $attributeData) {
            $this->destAttributeOldNewMap[$attributeData['attribute_id']] = $this->newAttributes[$key]['attribute_id'];
        }

        return $this->newAttributes;
    }

    /**
     * @param int $sourceAttributeId
     * @return mixed
     */
    protected function getDestinationAttributeId($sourceAttributeId)
    {
        $id = null;
        $key = null;
        if (isset($this->initialAttributes['source'][$sourceAttributeId])) {
            $key = $this->initialAttributes['source'][$sourceAttributeId]['entity_type_id'] . '-'
                . $this->initialAttributes['source'][$sourceAttributeId]['attribute_code'];
        }

        if ($key && isset($this->initialAttributes['dest'][$key])) {
            $id = $this->initialAttributes['dest'][$key]['attribute_id'];
        }

        return $id;
    }

    /**
     * @return array
     */
    protected function getDocumentsList()
    {
        return [
            'eav_attribute_group' => [],
            'eav_attribute_set' => [],
            'eav_attribute' => [],
            'eav_entity_attribute' => [],
            'catalog_eav_attribute' => ['attribute_id'],
            'customer_eav_attribute' => ['attribute_id'],
            'eav_entity_type' => ['entity_type_id'],
            'customer_eav_attribute_website' => [],
            'eav_attribute_label' => [],
            'eav_attribute_option' => [],
            'eav_attribute_option_value' => [],
            'eav_entity' => [],
            'eav_entity_datetime' => [],
            'eav_entity_decimal' => [],
            'eav_entity_int' => [],
            'eav_entity_store' => [],
            'eav_entity_text' => [],
            'eav_entity_varchar' => [],
            'eav_form_element' => [],
            'eav_form_fieldset' => [],
            'eav_form_fieldset_label' => [],
            'eav_form_type' => [],
            'eav_form_type_entity' => [],
            'enterprise_rma_item_eav_attribute' => [],
            'enterprise_rma_item_eav_attribute_website' => []
        ];
    }

    /**
     * @inheritdoc
     */
    public function getMaxSteps()
    {
        return 3;
    }
}
