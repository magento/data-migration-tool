<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Record;

class Helper
{
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
     * @var []
     */
    protected $eavAttributes;

    /**
     * @var []
     */
    protected $skipAttributes;

    /**
     * @var []
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
     * @param string $document
     * @return string|null
     * @throws \Migration\Exception
     */
    public function getAttributeType($document)
    {
        if (empty($this->documentAttributeTypes)) {
            $entities = array_keys($this->readerGroups->getGroup('eav_entities'));
            foreach ($entities as $entity) {
                $documents = $this->readerGroups->getGroup($entity);
                foreach ($documents as $item => $key) {
                    $this->documentAttributeTypes[$item] = $entity;
                    $this->initEavEntity($entity, $item, $key);
                }
            }
        }
        return isset($this->documentAttributeTypes[$document]) ? $this->documentAttributeTypes[$document] : null;
    }

    /**
     * @param string $attributeType
     * @param string $sourceDocName
     * @param [] $recordData
     * @return bool
     */
    public function isSkipRecord($attributeType, $sourceDocName, $recordData)
    {
        if (!isset($this->sourceDocuments[$sourceDocName])
            || $this->sourceDocuments[$sourceDocName] != 'value_id'
            || !isset($recordData['attribute_id'])
        ) {
            return false;
        }
        return isset($this->skipAttributes[$attributeType][$recordData['attribute_id']]);
    }

    /**
     * @param string $attributeType
     * @param string $sourceDocName
     * @param Record\Collection $destinationRecords
     * @return void
     */
    public function updateAttributeData($attributeType, $sourceDocName, Record\Collection $destinationRecords)
    {
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
        foreach ($attributeCodes as $attribute) {
            if (isset($this->eavAttributes[$attributeType][$attribute])) {
                $attributeId = $this->eavAttributes[$attributeType][$attribute]['attribute_id'];
                $attributeIdsByType[$this->eavAttributes[$attributeType][$attribute]['backend_type']][] = $attributeId;
                $attributeCodesById[$attributeId] = $attribute;
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

        $this->setAttributeData($destinationRecords, $recordAttributesData, $attributeCodes);
    }

    public function setAttributeData(
        Record\Collection $destinationRecords,
        array $recordAttributesData,
        array $attributeCodes
    ) {
        /** @var Record $record */
        foreach ($destinationRecords as $record) {
            if (isset($recordAttributesData[$record->getValue('entity_id')])) {
                if ($this->configReader->getOption(self::UPGRADE_CUSTOMER_PASSWORD_HASH)) {
                    $recordAttributesData = $this->upgradeCustomerHash(
                        $recordAttributesData,
                        $record->getValue('entity_id')
                    );
                }
                $data = $record->getData();
                $data = array_merge(
                    array_fill_keys($attributeCodes, null),
                    $data,
                    $recordAttributesData[$record->getValue('entity_id')]
                );
                $record->setData($data);
            }
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
        $attributeType = $this->getAttributeType($document);

        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                [
                    'et' => $this->source->addDocumentPrefix($document)
                ],
                'COUNT(*)'
            )
            ->where('et.attribute_id NOT IN (?)', array_keys($this->skipAttributes[$attributeType]));
        $count = $query->getAdapter()->fetchOne($query);

        return $count;
    }

    /**
     * @param string $attributeType
     * @param string $document
     * @param string $key
     * @return void
     * @throws \Migration\Exception
     */
    protected function initEavEntity($attributeType, $document, $key)
    {
        if ($key != 'entity_id') {
            return;
        }
        $this->initEavAttributes($attributeType);
        foreach (array_keys($this->readerAttributes->getGroup($document)) as $attribute) {
            if (!isset($this->eavAttributes[$attributeType][$attribute]['attribute_id'])) {
                if (isset($this->eavAttributes[$attributeType])) {
                    $message = sprintf('Attribute %s does not exist in the type %s', $attribute, $attributeType);
                } else {
                    $message = sprintf('Attribute type %s does not exist', $attributeType);
                }
                throw new \Migration\Exception($message);
            }
            $attributeId = $this->eavAttributes[$attributeType][$attribute]['attribute_id'];
            $attributeCode = $this->eavAttributes[$attributeType][$attribute]['attribute_code'];
            $this->skipAttributes[$attributeType][$attributeId] = $attributeCode;
        }
    }

    /**
     * @param string $attributeType
     * @return void
     */
    protected function initEavAttributes($attributeType)
    {
        if (isset($this->eavAttributes[$attributeType])) {
            return;
        }

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
                $attributeType
            );
        $attributes = $query->getAdapter()->fetchAll($query);

        foreach ($attributes as $attribute) {
            $this->eavAttributes[$attributeType][$attribute['attribute_code']] = $attribute;
            $this->eavAttributes[$attributeType][$attribute['attribute_id']] = $attribute;
        }
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
     * @param string $entityId
     * @return array
     */
    private function upgradeCustomerHash($recordAttributesData, $entityId)
    {
        if (isset($recordAttributesData[$entityId]['password_hash'])) {
            list($hash, $salt) = explode(':', $recordAttributesData[$entityId]['password_hash'], 2);

            if (strlen($hash) == 32) {
                $recordAttributesData[$entityId]['password_hash'] = implode(':', [$hash, $salt, '0']);
            } elseif (strlen($hash) == 64) {
                $recordAttributesData[$entityId]['password_hash'] = implode(':', [$hash, $salt, '1']);
            }
        }

        return $recordAttributesData;
    }
}
