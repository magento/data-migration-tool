<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

/**
 * Record iterator class
 */
class RecordCollection extends \Migration\Resource\AbstractCollection
{
    /**
     * @param Record $record
     */
    public function addRecord($record)
    {
        $this->data[] = $record;
    }

    /**
     * @param $columnName
     * @return array
     */
    public function getValue($columnName)
    {
        $result = [];
        foreach($this->data as $item) {
            $result[] = $item->getValue($columnName);
        }
        return $result;
    }

    public function setValue($columnName, $value)
    {
        foreach($this->data as $item) {
            $item->setValue($columnName, $value);
        }
        return $this;
    }
}
