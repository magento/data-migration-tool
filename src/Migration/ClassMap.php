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
    protected $map = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $className
     * @return mixed
     */
    public function convertClassName($className)
    {
        if (is_string($className) && array_key_exists($className, $this->getMap())) {
            return $this->getMap()[$className];
        }
        return $className;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getMap()
    {
        if ($this->map === null) {
            $classMapFile = __DIR__ . '/../../' . $this->config->getOption('class_map');
            if (!file_exists($classMapFile)) {
                throw new Exception('Invalid class map file name.');
            }
            $this->map = include $classMapFile;
        }
        return $this->map;
    }
}
