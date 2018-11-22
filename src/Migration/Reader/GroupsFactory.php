<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Factory class for @see \Migration\Reader\Groups
 */
class GroupsFactory
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
        $instanceName = \Migration\Reader\Groups::class
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->instanceName = $instanceName;
    }

    /**
     * Create groups from file specified in config option
     *
     * @param string $configOption
     * @return \Migration\Reader\Groups
     */
    public function create($configOption)
    {
        $groupsFile = $this->config->getOption($configOption);
        return $this->objectManager->create($this->instanceName, ['groupsFile' => $groupsFile]);
    }
}
