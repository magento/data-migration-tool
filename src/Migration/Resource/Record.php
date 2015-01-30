<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

/**
 * Record iterator class
 */
class Record
{
    /**
     * @var array
     */
    protected $data;

    public function getValue($columnName)
    {
        return $this->data[$columnName];
    }

    public function setValue($columnName, $value)
    {
        $this->data[$columnName] = $value;
        return $this;
    }
}
