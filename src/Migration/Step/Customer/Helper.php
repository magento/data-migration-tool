<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Record;

class Helper
{
    /**#@+
     * Exploded password hash keys
     */
    const PASSWORD_HASH = 0;
    const PASSWORD_SALT = 1;
    /**#@-*/

    /**
     * @var array
     */
    protected $documentAttributeTypes;

    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerAttributes;

    /**
     * @var array
     */
    protected $eavAttributes;

    /**
     * @var array
     */
    protected $skipAttributes;

    /**
     * @var array
     */
    protected $sourceDocuments;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    const UPGRADE_CUSTOMER_PASSWORD_HASH = 'upgrade_customer_password_hash';

    /**
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param \Migration\Config $configReader
     */
    public function __construct(
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        GroupsFactory $groupsFactory,
        \Migration\Config $configReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->readerAttributes = $groupsFactory->create('customer_attribute_groups_file');
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->sourceDocuments = $this->readerGroups->getGroup('source_documents');
        $this->configReader = $configReader;
    }

    /**
     * Retrieves entity type code by document name
     *
     * @param string $sourceDocName
     * @return string|null
     * @throws \Migration\Exception
     */
    public function getEntityTypeCodeByDocumentName($sourceDocName)
    {
        if (empty($this->documentAttributeTypes)) {
            $entityTypeCodes = array_keys($this->readerGroups->getGroup('eav_entities'));
            foreach ($entityTypeCodes as $entityTypeCode) {
                $documents = $this->readerGroups->getGroup($entityTypeCode);
                $documents = array_keys($documents);
                foreach ($documents as $documentName) {
                    $this->documentAttributeTypes[$documentName] = $entityTypeCode;
                }
            }
        }
        return isset($this->documentAttributeTypes[$sourceDocName]) ?
            $this->documentAttributeTypes[$sourceDocName] :
            null;
    }

    /**
     * Retrieves whether record should be skipped in moving from source to destination
     *
     * @param string $entityTypeCode
     * @param string $sourceDocName
     * @param [] $recordData
     * @return bool
     */
    public function isSkipRecord($entityTypeCode, $sourceDocName, $recordData)
    {
        if (!isset($this->sourceDocuments[$sourceDocName])
            || $this->sourceDocuments[$sourceDocName] != 'value_id'
            || !isset($recordData['attribute_id'])
        ) {
            return false;
        }

        $skippedRecords = $this->getSkippedAttributes();
        return isset($skippedRecords[$entityTypeCode][$recordData['attribute_id']]);
    }

    /**
     * Retrieves array with attributes which should be skipped
     *
     * Sample of returning array:
     * <code>
     * [
     *      'customer' => [
     *          3 => 'created_in',
     *          4 => 'prefix',
     *          5 => 'firstname'
     *      ],
     *      'customer_address' => [
     *          26 => 'city',
     *          24 => 'company',
     *          27 => 'country_id'
     *      ]
     * ]
     * </code>
     * @return array
     * @throws \Migration\Exception
     */
    public function getSkippedAttributes()
    {
        if ($this->skipAttributes === null) {
            $this->skipAttributes = [];
            $entityTypeCodes = array_keys($this->readerGroups->getGroup('eav_entities'));
            foreach ($entityTypeCodes as $entityTypeCode) {
                $documents = $this->readerGroups->getGroup($entityTypeCode);
                foreach ($documents as $documentName => $key) {
                    if ($key != 'entity_id') {
                        continue;
                    }
                    foreach (array_keys($this->readerAttributes->getGroup($documentName)) as $attributeCode) {
                        $eavAttributes = $this->getAttributesData($entityTypeCode);
                        if (!isset($eavAttributes[$attributeCode])) {
                            throw new \Migration\Exception(
                                sprintf(
                                    'Attribute %s does not exist in the type %s',
                                    $attributeCode,
                                    $entityTypeCode
                                )
                            );
                        }
                        $attributeId = $eavAttributes[$attributeCode]['attribute_id'];
                        $this->skipAttributes[$entityTypeCode][$attributeId] = $attributeCode;
                    }
                }
            }
        }
        return $this->skipAttributes;
    }

    /**
     * @param string $entityTypeCode
     * @param string $sourceDocName
     * @param string $destinationDocName
     * @param Record\Collection $destinationRecords
     * @return void
     */
    public function updateAttributeData(
        $entityTypeCode,
        $sourceDocName,
        $destinationDocName,
        Record\Collection $destinationRecords
    ) {
        if (!isset($this->sourceDocuments[$sourceDocName]) || $this->sourceDocuments[$sourceDocName] != 'entity_id') {
            return;
        }
        $records = [];
        /** @var Record $record */
        foreach ($destinationRecords as $record) {
            $records[] = $record->getValue('entity_id');
        }

        $attributeIdsByType = [];
        $attributeCodesById = [];
        $attributeCodes = array_keys($this->readerAttributes->getGroup($sourceDocName));
        foreach ($attributeCodes as $attributeCode) {
            $eavAttributes = $this->getAttributesData($entityTypeCode);
            if (is_array($eavAttributes) && isset($eavAttributes[$attributeCode])) {
                $attributeId = $eavAttributes[$attributeCode]['attribute_id'];
                $attributeBackendType = $eavAttributes[$attributeCode]['backend_type'];
                $attributeIdsByType[$attributeBackendType][] = $attributeId;
                $attributeCodesById[$attributeId] = $attributeCode;
            }
        }

        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $selects = [];
        foreach (array_keys($attributeIdsByType) as $type) {
            $select = $adapter->getSelect()
                ->from(
                    ['et' => $this->source->addDocumentPrefix($sourceDocName . '_' . $type)],
                    ['entity_id', 'attribute_id', 'value']
                )
            ->where('et.entity_id in (?)', $records)
            ->where('et.attribute_id in (?)', $attributeIdsByType[$type]);
            $selects[] = $select;
        }
        $query = $adapter->getSelect()->union($selects, \Zend_Db_Select::SQL_UNION_ALL);

        $recordAttributesData = $this->sortAttributesData(
            $query->getAdapter()->fetchAll($query),
            $attributeCodesById
        );

        $fieldsWithDefaults = $this->getFieldsWithDefaultValues($attributeCodes, $destinationDocName);
        $this->setAttributeData($destinationRecords, $recordAttributesData, $fieldsWithDefaults);
    }

    /**
     * Retrieves destination document fields with valid default values
     *
     * @param array $attributeCodes
     * @param string $destinationDocName
     * @return array
     * @throws \Migration\Exception
     */
    protected function getFieldsWithDefaultValues($attributeCodes, $destinationDocName)
    {
        /** @var Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $structure = $adapter->getDocumentStructure($destinationDocName);

        $fields = [];
        foreach ($attributeCodes as $attributeCode) {
            if (!isset($structure[$attributeCode])) {
                throw new \Migration\Exception(
                    sprintf('Destination document %s does not have attribute %s', $destinationDocName, $attributeCode)
                );
            }
            $fields[$attributeCode] =
                $structure[$attributeCode]['DEFAULT'] === null && $structure[$attributeCode]['NULLABLE'] === false ?
                    '' :
                    null;
        }
        return $fields;
    }

    /**
     * @param Record\Collection $destinationRecords
     * @param array $recordAttributesData
     * @param array $fieldsWithDefaults
     * @return void
     */
    public function setAttributeData(
        Record\Collection $destinationRecords,
        array $recordAttributesData,
        array $fieldsWithDefaults
    ) {
        /** @var Record $record */
        foreach ($destinationRecords as $record) {
            $recordEntityData = [];
            if (isset($recordAttributesData[$record->getValue('entity_id')])) {
                $recordEntityData = $recordAttributesData[$record->getValue('entity_id')];
                if ($this->configReader->getOption(self::UPGRADE_CUSTOMER_PASSWORD_HASH)) {
                    $recordEntityData = $this->upgradeCustomerHash($recordEntityData);
                }
            }
            $data = $record->getData();
            $data = array_merge($fieldsWithDefaults, $data, $recordEntityData);
            $record->setData($data);
        }
    }

    /**
     * @param array $data
     * @param array $attributeCodesById
     * @return array
     */
    protected function sortAttributesData($data, $attributeCodesById)
    {
        $result = [];
        foreach ($data as $entityData) {
            $attributeCode = $attributeCodesById[$entityData['attribute_id']];
            $result[$entityData['entity_id']][$attributeCode] = $entityData['value'];
        }
        return $result;
    }

    /**
     * @param string $document
     * @return int
     */
    public function getSourceRecordsCount($document)
    {
        if ($this->sourceDocuments[$document] == 'entity_id') {
            return $this->source->getRecordsCount($document);
        }
        $attributeType = $this->getEntityTypeCodeByDocumentName($document);
        $skipAttributes = $this->getSkippedAttributes();

        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                [
                    'et' => $this->source->addDocumentPrefix($document)
                ],
                'COUNT(*)'
            )
            ->where('et.attribute_id NOT IN (?)', array_keys($skipAttributes[$attributeType]));
        $count = $query->getAdapter()->fetchOne($query);

        return $count;
    }

    /**
     * Retrieves data for all attributes relative to $entityTypeCode entity type
     *
     * @param string $entityTypeCode
     * @return array
     */
    protected function getAttributesData($entityTypeCode)
    {
        if (!isset($this->eavAttributes[$entityTypeCode])) {
            $this->eavAttributes[$entityTypeCode] = [];
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()
                ->from(
                    ['et' => $this->source->addDocumentPrefix('eav_entity_type')],
                    []
                )->join(
                    ['ea' => $this->source->addDocumentPrefix('eav_attribute')],
                    'et.entity_type_id = ea.entity_type_id',
                    [
                        'attribute_id',
                        'backend_type',
                        'attribute_code',
                        'entity_type_id'
                    ]
                )->where(
                    'et.entity_type_code = ?',
                    $entityTypeCode
                );
            $attributes = $query->getAdapter()->fetchAll($query);

            foreach ($attributes as $attribute) {
                $this->eavAttributes[$entityTypeCode][$attribute['attribute_code']] = $attribute;
            }
        }
        return $this->eavAttributes[$entityTypeCode];
    }

    /**
     * @throws \Zend_Db_Adapter_Exception
     * @return void
     */
    public function updateEavAttributes()
    {
        /** @var Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $query = $adapter->getSelect()
            ->from($this->destination->addDocumentPrefix('eav_entity_type'), ['entity_type_id', 'entity_type_code']);
        $entityTypes = $query->getAdapter()->fetchAll($query);
        $entityTypesByCode = [];
        foreach ($entityTypes as $entityType) {
            $entityTypesByCode[$entityType['entity_type_code']] = $entityType['entity_type_id'];
        }

        $entities = array_keys($this->readerGroups->getGroup('eav_entities'));
        foreach ($entities as $entity) {
            $documents = $this->readerGroups->getGroup($entity);
            foreach ($documents as $document => $key) {
                if ($key != 'entity_id') {
                    continue;
                }

                $codes = implode("','", array_keys($this->readerAttributes->getGroup($document)));
                $where = [
                    sprintf("attribute_code IN ('%s')", $codes),
                    sprintf("entity_type_id = '%s'", $entityTypesByCode[$entity])
                ];
                $adapter->getSelect()->getAdapter()->update(
                    $this->destination->addDocumentPrefix('eav_attribute'),
                    ['backend_type' => 'static'],
                    $where
                );
            }
        }
    }

    /**
     * Upgrade customer hash according M2 algorithm versions
     *
     * @param array $recordAttributesData
     * @return array
     */
    private function upgradeCustomerHash($recordAttributesData)
    {
        if (isset($recordAttributesData['password_hash'])) {
            $hash = $this->explodePasswordHash($recordAttributesData['password_hash']);

            if (strlen($hash[self::PASSWORD_HASH]) == 32) {
                $recordAttributesData['password_hash'] = implode(
                    ':',
                    [$hash[self::PASSWORD_HASH], $hash[self::PASSWORD_SALT], '0']
                );
            } elseif (strlen($hash[self::PASSWORD_HASH]) == 64) {
                $recordAttributesData['password_hash'] = implode(
                    ':',
                    [$hash[self::PASSWORD_HASH], $hash[self::PASSWORD_SALT], '1']
                );
            }
        }

        return $recordAttributesData;
    }

    /**
     * @param string $passwordHash
     * @return array
     */
    private function explodePasswordHash($passwordHash)
    {
        $explodedPassword = explode(':', $passwordHash, 2);
        $explodedPassword[self::PASSWORD_SALT] = isset($explodedPassword[self::PASSWORD_SALT])
            ? $explodedPassword[self::PASSWORD_SALT]
            : ''
        ;
        return $explodedPassword;
    }
}
