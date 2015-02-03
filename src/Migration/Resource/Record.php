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

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get column value
     *
     * @param string $columnName
     * @return mixed
     */
    public function getValue($columnName)
    {
        return $this->data[$columnName];
    }

    /**
     * Set column value
     *
     * @param string $columnName
     * @param mixed $value
     * @return $this
     */
    public function setValue($columnName, $value)
    {
        $this->data[$columnName] = $value;
        return $this;
    }

    /**
     * Set record data
     *
     * @param array $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}
