<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Factory class for @see \Migration\Reader\Map
 */
class MapFactory
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
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Migration\Config $config
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Migration\Config $config,
        $instanceName = \Migration\Reader\Map::class
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->instanceName = $instanceName;
    }

    /**
     * Create map from file specified in config option
     *
     * @param string $configOption
     * @return \Migration\Reader\Map
     */
    public function create($configOption)
    {
        $mapFile = $this->config->getOption($configOption);
        return $this->objectManager->create($this->instanceName, ['mapFile' => $mapFile]);
    }
}
