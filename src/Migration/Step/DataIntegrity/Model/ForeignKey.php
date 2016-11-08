<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\DataIntegrity\Model;

use Migration\ResourceModel\Adapter\Mysql as Adapter;

/**
 * Class ForeignKey
 */
class ForeignKey
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
    protected $parentTableKey;

    /**
     * @var string
     */
    public $childTable;

    /**
     * @var string
     */
    protected $childTableKey;

    /**
     * @var int[]
     */
    protected $orphanedRowIds;

    /**
     * @param Adapter $adapter
     * @param string $keyName
     * @param string $parentTable
     * @param string $childTable
     * @param string $parentTableKey
     * @param string $childTableKey
     */
    public function __construct(
        Adapter $adapter,
        $keyName,
        $parentTable,
        $childTable,
        $parentTableKey,
        $childTableKey
    ) {
        $this->adapter = $adapter;
        $this->keyName = $keyName;
        $this->parentTable = $parentTable;
        $this->parentTableKey = $parentTableKey;
        $this->childTable = $childTable;
        $this->childTableKey = $childTableKey;
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
    public function getParentTableKey()
    {
        return $this->parentTableKey;
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
    public function getChildTableKey()
    {
        return $this->childTableKey;
    }

    /**
     * @return int[]
     */
    public function getOrphanedRowIds()
    {
        if ($this->orphanedRowIds === null) {
            $query = $this->adapter->getSelect()->from(
                ['child' => $this->childTable],
                $this->childTableKey
            )->joinLeft(
                ['parent' => $this->parentTable],
                'child.' . $this->childTableKey . ' = parent.' . $this->parentTableKey,
                null
            )->where(
                'child.' . $this->childTableKey . ' IS NOT NULL'
            )->where(
                'parent.' . $this->parentTableKey . ' IS NULL'
            );
            $this->orphanedRowIds = $query->getAdapter()->fetchCol($query);
        }
        return $this->orphanedRowIds;
    }
}
