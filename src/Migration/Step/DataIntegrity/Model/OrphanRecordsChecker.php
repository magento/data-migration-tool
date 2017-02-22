<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * @return string
     */
    public function getParentTable()
    {
        return $this->parentTable;
    }

    /**
     * @return string
     */
    public function getParentTableField()
    {
        return $this->parentTableField;
    }

    /**
     * @return string
     */
    public function getChildTable()
    {
        return $this->childTable;
    }

    /**
     * @return string
     */
    public function getChildTableField()
    {
        return $this->childTableField;
    }

    /**
     * @return bool
     */
    public function hasOrphanRecords()
    {
        return (bool)$this->getOrphanRecordsIds();
    }

    /**
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
