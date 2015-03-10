<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

/**
 * Class ClassMap
 */
class ClassMap
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @param string $className
     * @return mixed
     */
    public function convertClassName($className)
    {
        if (array_key_exists($className, $this->getMap())) {
            return $this->getMap()[$className];
        }
        return $className;
    }

    /**
     * @return array|mixed
     */
    public function getMap()
    {
        if (is_null($this->map)) {
            $this->map = include __DIR__ . '/../../etc/class_map.php';
        }
        return $this->map;
    }
}
