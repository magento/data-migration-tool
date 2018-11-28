<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer\Model;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Record;

/**
 * The class is responsible for fetching attribute values of customers
 * form the data tables (e.g. customer_entity_int, customer_entity_varchar, customer_address_entity_text ...)
 * and inserting into customers entity records,
 * which represents customer_entity and customer_address_entity tables
 */
class AttributesDataToCustomerEntityRecords
{
    /**#@+
     * Exploded password hash keys
     */
    const PASSWORD_HASH = 0;
    const PASSWORD_SALT = 1;
    /**#@-*/

    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerAttributes;

    /**
     * @var array
     */
    private $sourceDocuments;

    /**
     * @var \Migration\Config
     */
    private $configReader;

    /**
     * @var EntityTypeCode
     */
    private $entityTypeCode;

    /**
     * Option name from configuration file which indicates whether to update customer hash or not
     */
    const UPGRADE_CUSTOMER_PASSWORD_HASH = 'upgrade_customer_password_hash';

    /**
     * @param ResourceModel\Source $source
     * @param GroupsFactory $groupsFactory
     * @param \Migration\Config $configReader
     * @param EntityTypeCode $entityTypeCode
     */
    public function __construct(
        ResourceModel\Source $source,
        GroupsFactory $groupsFactory,
        \Migration\Config $configReader,
        EntityTypeCode $entityTypeCode
    ) {
        $this->source = $source;
        $this->readerAttributes = $groupsFactory->create('customer_attribute_groups_file');
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->sourceDocuments = $this->readerGroups->getGroup('source_documents');
        $this->configReader = $configReader;
        $this->entityTypeCode = $entityTypeCode;
    }

    /**
     * Fetch attribute values of customers
     * form the data tables (e.g. customer_entity_int, customer_entity_varchar, customer_address_entity_text ...)
     * and insert into customers entity records,
     * which represents customer_entity and customer_address_entity tables
     *
     * @param string $sourceDocName
     * @param Record\Collection $destinationRecords
     * @return void
     */
    public function updateCustomerEntities(
        $sourceDocName,
        Record\Collection $destinationRecords
    ) {
        $entityAttributesData = [];
        $attributeIdsByType = [];
        $attributeCodesById = [];
        $upgradeCustomerPassword = $this->configReader->getOption(self::UPGRADE_CUSTOMER_PASSWORD_HASH);
        $entityTypeCode = $this->entityTypeCode->getEntityTypeCodeByDocumentName($sourceDocName);
        $attributeCodes = array_keys($this->readerAttributes->getGroup($sourceDocName));
        foreach ($attributeCodes as $attributeCode) {
            $eavAttributes = $this->entityTypeCode->getAttributesData($entityTypeCode);
            if (is_array($eavAttributes) && isset($eavAttributes[$attributeCode])) {
                $attributeId = $eavAttributes[$attributeCode]['attribute_id'];
                $attributeBackendType = $eavAttributes[$attributeCode]['backend_type'];
                $attributeIdsByType[$attributeBackendType][] = $attributeId;
                $attributeCodesById[$attributeId] = $attributeCode;
            }
        }
        $attributesData = $this->fetchAttributesData($sourceDocName, $destinationRecords, $attributeIdsByType);
        foreach ($attributesData as $attributeData) {
            $attributeCode = $attributeCodesById[$attributeData['attribute_id']];
            if ($upgradeCustomerPassword && $attributeCode == 'password_hash') {
                $attributeData['value'] = $this->upgradeCustomerHash($attributeData['value']);
            }
            $entityAttributesData[$attributeData['entity_id']][$attributeCode] = $attributeData['value'];
        }
        $this->setAttributesData($destinationRecords, $entityAttributesData);
    }

    /**
     * Fetch attribute values of customers form the data tables
     * (e.g. customer_entity_int, customer_entity_varchar, customer_address_entity_text ...)
     *
     * @param mixed $sourceDocName
     * @param mixed $destinationRecords
     * @param mixed $attributeIdsByType
     * @return array
     */
    private function fetchAttributesData($sourceDocName, $destinationRecords, $attributeIdsByType)
    {
        $entityIds = [];
        /** @var Record $record */
        foreach ($destinationRecords as $record) {
            $entityIds[] = $record->getValue('entity_id');
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
                ->where('et.entity_id in (?)', $entityIds)
                ->where('et.attribute_id in (?)', $attributeIdsByType[$type]);
            $selects[] = $select;
        }
        $query = $adapter->getSelect()->union($selects, \Zend_Db_Select::SQL_UNION_ALL);
        return $query->getAdapter()->fetchAll($query);
    }

    /**
     * Insert values of attributes into customer entity records
     *
     * @param Record\Collection $destinationRecords
     * @param array $entityAttributesData
     * @return void
     */
    private function setAttributesData(
        Record\Collection $destinationRecords,
        array $entityAttributesData
    ) {
        /** @var Record $record */
        foreach ($destinationRecords as $record) {
            $recordEntityData = [];
            if (isset($entityAttributesData[$record->getValue('entity_id')])) {
                $recordEntityData = $entityAttributesData[$record->getValue('entity_id')];
            }
            $data = $record->getData();
            $dataDefault = $record->getDataDefault();
            $data = array_merge($dataDefault, $data, $recordEntityData);
            $record->setData($data);
        }
    }

    /**
     * Upgrade customer hash according M2 algorithm versions
     *
     * @param string $hash
     * @return array
     */
    private function upgradeCustomerHash($hash)
    {
        if (isset($hash)) {
            $hashExploded = $this->explodePasswordHash($hash);
            if (strlen($hashExploded[self::PASSWORD_HASH]) == 32) {
                $hash = implode(':', [$hashExploded[self::PASSWORD_HASH], $hashExploded[self::PASSWORD_SALT], '0']);
            } elseif (strlen($hashExploded[self::PASSWORD_HASH]) == 64) {
                $hash = implode(':', [$hashExploded[self::PASSWORD_HASH], $hashExploded[self::PASSWORD_SALT], '1']);
            }
        }
        return $hash;
    }

    /**
     * Split password hash to hash part and salt part
     *
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
