<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

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
