<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Factory class for @see \Migration\Reader\Lists
 */
class ListsFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName = null;

    /**
     * Instance name to create
     *
     * @var \Migration\Config
     */
    protected $config;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Migration\Config $config
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Migration\Config $config,
        $instanceName = '\\Migration\\Reader\\Lists'
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->instanceName = $instanceName;
    }

    /**
     * Create lists file specified in config option
     *
     * @param string $configOption
     * @return \Migration\Reader\Lists
     */
    public function create($configOption)
    {
        $listsFile = $this->config->getOption($configOption);
        return $this->objectManager->create($this->instanceName, ['listsFile' => $listsFile]);
    }
}
