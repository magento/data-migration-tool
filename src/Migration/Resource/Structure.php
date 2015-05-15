<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

/**
 * Document class
 */
class Structure
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
     * Check field exists in Structure
     *
     * @param string $name
     * @return bool
     */
    public function hasField($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Get Structure field
     *
     * @return array
     */
    public function getFields()
    {
        return $this->data;
    }
}
