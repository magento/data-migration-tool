<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Adapter\Mysql;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var Destination
     */
    private $destination;

    /**
     * @var string
     */
    private $eavEntityStoreTable = 'eav_entity_store';

    /**
     * @var string
     */
    private $storeTable = 'core_store';

    /**
     * @var array
     */
    private $sequenceMetaTable = [
        'name' => 'sales_sequence_meta',
        'structure' => [
            'meta_id',
            'entity_type',
            'store_id',
            'sequence_table'
        ]
    ];

    /**
     * @var array
     */
    private $sequenceProfileTable = [
        'name' => 'sales_sequence_profile',
        'structure' => [
            'profile_id',
            'meta_id',
            'prefix',
            'suffix',
            'start_value',
            'step',
            'max_value',
            'warning_value',
            'is_active'
        ]
    ];

    /**
     * @var array
     */
    private $entityTypeTablesMap = [
        [
            'entity_type_code' => 'order',
            'entity_type_table' => 'sequence_order',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'invoice',
            'entity_type_table' => 'sequence_invoice',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'creditmemo',
            'entity_type_table' => 'sequence_creditmemo',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'shipment',
            'entity_type_table' => 'sequence_shipment',
            'column' => 'sequence_value'
        ], [
            'entity_type_code' => 'rma_item',
            'entity_type_table' => 'sequence_rma_item',
            'column' => 'sequence_value'
        ]
    ];

    /**
     * @param Source $source
     * @param Destination $destination
     */
    public function __construct(
        Source $source,
        Destination $destination
    ) {
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * Get increment for entity type
     *
     * @param int $entityTypeId
     * @param int $storeId
     * @return bool|int
     */
    public function getIncrementForEntityType($entityTypeId, $storeId)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from(
            $this->source->addDocumentPrefix($this->eavEntityStoreTable),
            ['increment_prefix', 'increment_last_id']
        )->where(
            'entity_type_id = ?',
            $entityTypeId
        )->where(
            'store_id = (?)',
            $storeId
        );
        $data = $query->getAdapter()->fetchRow($query);
        if (!$data) {
            return false;
        }
        $incrementNumber = (int) substr($data['increment_last_id'], strlen($data['increment_prefix']));
        return $incrementNumber;
    }

    /**
     * Get store ids
     *
     * @return array
     */
    public function getStoreIds()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from($this->source->addDocumentPrefix($this->storeTable), ['store_id']);
        return $query->getAdapter()->fetchCol($query);
    }

    /**
     * Get entity type tables map
     *
     * @return array
     */
    public function getEntityTypeTablesMap()
    {
        $entityIds = $this->getEntityTypeIdByCode(array_column($this->entityTypeTablesMap, 'entity_type_code'));
        foreach ($this->entityTypeTablesMap as &$entityTypeTable) {
            $entityTypeTable['entity_type_id'] = isset($entityIds[$entityTypeTable['entity_type_code']])
                ? $entityIds[$entityTypeTable['entity_type_code']]
                : null;
        }
        return $this->entityTypeTablesMap;
    }

    /**
     * Get entity type data
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    public function getEntityTypeData($key, $value)
    {
        foreach ($this->getEntityTypeTablesMap() as $entityType) {
            if (isset($entityType[$key]) && $entityType[$key] == $value) {
                return $entityType;
            }
        }
        return [];
    }

    /**
     * Get sequence meta table
     *
     * @param bool $structure
     * @return string|array
     */
    public function getSequenceMetaTable($structure = false)
    {
        return $structure ? $this->sequenceMetaTable['structure'] : $this->sequenceMetaTable['name'];
    }

    /**
     * Get sequence profile table
     *
     * @param bool $structure
     * @return string|array
     */
    public function getSequenceProfileTable($structure = false)
    {
        return $structure ? $this->sequenceProfileTable['structure'] : $this->sequenceProfileTable['name'];
    }

    /**
     * Get table name
     *
     * @param string $table
     * @param bool $storeId
     * @return string
     */
    public function getTableName($table, $storeId = false)
    {
        return ($storeId !== false)
            ? $this->destination->addDocumentPrefix($table) . '_' . $storeId
            : $this->destination->addDocumentPrefix($table);
    }

    /**
     * Get entity type id by code
     *
     * @param array $entityTypeCodes
     * @return array
     */
    private function getEntityTypeIdByCode($entityTypeCodes)
    {
        /** @var Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $query = $adapter->getSelect()
            ->from(
                ['et' => $this->destination->addDocumentPrefix('eav_entity_type')],
                ['entity_type_id', 'entity_type_code']
            )
            ->where('et.entity_type_code IN (?)', $entityTypeCodes);
        $entityTypeIds = [];
        foreach ($query->getAdapter()->fetchAll($query) as $record) {
            $entityTypeIds[$record['entity_type_code']] = $record['entity_type_id'];
        }
        return $entityTypeIds;
    }
}
