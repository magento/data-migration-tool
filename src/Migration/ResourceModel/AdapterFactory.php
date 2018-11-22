<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory for resource adapter
 */
class AdapterFactory
{
    /**
     * @var string
     */
    protected $defaultClassName = \Migration\ResourceModel\Adapter\Mysql::class;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param \Migration\Config $configReader
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Migration\Config $configReader
    ) {
        $this->objectManager = $objectManager;
        $this->configReader = $configReader;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return AdapterInterface
     */
    public function create(array $data = [])
    {
        $classNameInConfig = $this->configReader->getOption('resource_adapter_class_name');
        $className = !empty($classNameInConfig) ? $classNameInConfig : $this->defaultClassName;
        return $this->objectManager->create($className, $data);
    }
}
