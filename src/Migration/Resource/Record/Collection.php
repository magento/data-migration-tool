<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

/**
 * Record iterator class
 */
class Collection extends \Migration\Resource\AbstractCollection
{
    /**
     * @var \Migration\Resource\Record[]
     */
    protected $data;

    /**
     * Add Record to collection
     *
     * @param \Migration\Resource\Record $record
     * @return $this
     */
    public function addRecord($record)
    {
        $this->data[] = $record;
        return $this;
    }

    /**
     * Get column data
     *
     * @param string $columnName
     * @return array
     */
    public function getValue($columnName)
    {
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
     */
    public function setValue($columnName, $value)
    {
        foreach ($this->data as $item) {
            $item->setValue($columnName, $value);
        }
        return $this;
    }
}
