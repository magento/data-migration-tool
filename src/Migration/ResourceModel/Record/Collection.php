<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Record;

use Migration\Exception;

/**
 * Record iterator class
 */
class Collection extends \Migration\ResourceModel\AbstractCollection
{
    /**
     * @var \Migration\ResourceModel\Record[]
     */
    protected $data;

    /**
     * @var \Migration\ResourceModel\Structure
     */
    protected $structure;

    /**
     * @param \Migration\ResourceModel\Structure $structure
     * @param array $data
     */
    public function __construct(\Migration\ResourceModel\Structure $structure, array $data = [])
    {
        $this->structure = $structure;
        parent::__construct($data);
    }

    /**
     * Set structure
     *
     * @return \Migration\ResourceModel\Structure
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * Add Record to collection
     *
     * @param \Migration\ResourceModel\Record $record
     * @return $this
     * @throws Exception
     */
    public function addRecord($record)
    {
        if (!$record->getStructure()) {
            $record->setStructure($this->structure);
        }
        if (!$record->validateStructure($this->structure)) {
            throw new Exception("Record structure does not equal Collection structure");
        }

        $this->data[] = $record;
        return $this;
    }

    /**
     * Get column data
     *
     * @param string $columnName
     * @return array
     * @throws Exception
     */
    public function getValue($columnName)
    {
        if ($this->structure && !$this->structure->hasField($columnName)) {
            throw new Exception("Collection Structure does not contain field $columnName");
        }
        $result = [];
        foreach ($this->data as $item) {
            $result[] = $item->getValue($columnName);
        }
        return $result;
    }

    /**
     * Set column data
     *
     * @param string $columnName
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function setValue($columnName, $value)
    {
        if ($this->structure && !$this->structure->hasField($columnName)) {
            throw new Exception("Collection Structure does not contain field $columnName");
        }
        foreach ($this->data as $item) {
            $item->setValue($columnName, $value);
        }
        return $this;
    }
}
