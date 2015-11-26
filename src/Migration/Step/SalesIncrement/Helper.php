<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var string
     */
    protected $eavEntityStore = 'eav_entity_store';

    /**
     * @var string
     */
    protected $storeTable = 'core_store';

    /**
     * @var array
     */
    protected $sequenceMetaTable = [
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
    protected $sequenceProfileTable = [
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
    protected $entityTypeTablesMap = [
        [
            'entity_type_id' => 5,
            'entity_type_code' => 'order',
            'entity_type_table' => 'sequence_order',
            'column' => 'sequence_value'
        ], [
            'entity_type_id' => 6,
            'entity_type_code' => 'invoice',
            'entity_type_table' => 'sequence_invoice',
            'column' => 'sequence_value'
        ], [
            'entity_type_id' => 7,
            'entity_type_code' => 'creditmemo',
            'entity_type_table' => 'sequence_creditmemo',
            'column' => 'sequence_value'
        ], [
            'entity_type_id' => 8,
            'entity_type_code' => 'shipment',
            'entity_type_table' => 'sequence_shipment',
            'column' => 'sequence_value'
        ], [
            'entity_type_id' => 9,
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
     * @param int $entityTypeId
     * @return bool|int
     */
    public function getMaxIncrementForEntityType($entityTypeId)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from(
            $this->source->addDocumentPrefix($this->eavEntityStore),
            ['increment_prefix', 'increment_last_id']
        )->where('entity_type_id = ?', $entityTypeId);
        $data = $query->getAdapter()->fetchAll($query);
        if (!$data) {
            return false;
        }
        $cutPrefixFunction = function (array $data) {
            return (int) substr($data['increment_last_id'], strlen($data['increment_prefix']));
        };
        return max(array_map($cutPrefixFunction, $data));
    }

    /**
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
     * @return array
     */
    public function getEntityTypeTablesMap()
    {
        return $this->entityTypeTablesMap;
    }

    /**
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
     * @param bool $structure
     * @return string|array
     */
    public function getSequenceMetaTable($structure = false)
    {
        return $structure ? $this->sequenceMetaTable['structure'] : $this->sequenceMetaTable['name'];
    }

    /**
     * @param bool $structure
     * @return string|array
     */
    public function getSequenceProfileTable($structure = false)
    {
        return $structure ? $this->sequenceProfileTable['structure'] : $this->sequenceProfileTable['name'];
    }

    /**
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
}
