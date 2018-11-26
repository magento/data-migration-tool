<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\DataIntegrity\Model;

use Migration\ResourceModel\Adapter\Mysql as Adapter;

/**
 * Class OrphanRecordsChecker
 */
class OrphanRecordsChecker
{
    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $keyName;

    /**
     * @var string
     */
    protected $parentTable;

    /**
     * @var string
     */
    protected $parentTableField;

    /**
     * @var string
     */
    public $childTable;

    /**
     * @var string
     */
    protected $childTableField;

    /**
     * @var int[]
     */
    protected $orphanRecordsIds;

    /**
     * @param Adapter $adapter
     * @param string $keyName
     * @param string $parentTable
     * @param string $childTable
     * @param string $parentTableField
     * @param string $childTableField
     */
    public function __construct(
        Adapter $adapter,
        $keyName,
        $parentTable,
        $childTable,
        $parentTableField,
        $childTableField
    ) {
        $this->adapter = $adapter;
        $this->keyName = $keyName;
        $this->parentTable = $parentTable;
        $this->parentTableField = $parentTableField;
        $this->childTable = $childTable;
        $this->childTableField = $childTableField;
    }

    /**
     * Get key name
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * Get parent table
     *
     * @return string
     */
    public function getParentTable()
    {
        return $this->parentTable;
    }

    /**
     * Get parent table field
     *
     * @return string
     */
    public function getParentTableField()
    {
        return $this->parentTableField;
    }

    /**
     * Get child table
     *
     * @return string
     */
    public function getChildTable()
    {
        return $this->childTable;
    }

    /**
     * Get child table field
     *
     * @return string
     */
    public function getChildTableField()
    {
        return $this->childTableField;
    }

    /**
     * Has orphan records
     *
     * @return bool
     */
    public function hasOrphanRecords()
    {
        return (bool)$this->getOrphanRecordsIds();
    }

    /**
     * Get orphan records ids
     *
     * @return int[]
     */
    public function getOrphanRecordsIds()
    {
        if ($this->orphanRecordsIds === null) {
            $query = $this->adapter->getSelect()->from(
                ['child' => $this->childTable],
                $this->childTableField
            )->joinLeft(
                ['parent' => $this->parentTable],
                'child.' . $this->childTableField . ' = parent.' . $this->parentTableField,
                null
            )->where(
                'child.' . $this->childTableField . ' IS NOT NULL'
            )->where(
                'parent.' . $this->parentTableField . ' IS NULL'
            )->distinct(
                true
            );
            $this->orphanRecordsIds = $query->getAdapter()->fetchCol($query);
        }
        return $this->orphanRecordsIds;
    }
}
