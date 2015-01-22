<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Abstract class for source and destination classes
 */
abstract class AbstractResource
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $resourceAdapter;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var string
     */
    protected $resourceUnitName;

    /**
     * @var int
     */
    protected $bulkSize = 0;

    /**
     * Constructor
     *
     * @param AdapterFactory $adapterFactory
     * @param \Migration\Config $configReader
     */
    public function __construct(
        \Migration\Resource\AdapterFactory $adapterFactory,
        \Migration\Config $configReader

    ) {
        $config['config'] = $this->getResourceConfig($configReader);
        $this->resourceAdapter = $adapterFactory->create($config);
        $this->bulkSize = $configReader->getOption('bulk_size');
    }

    /**
     * Returns definition of resource object
     *
     * @return array
     */
    public function getDataDefinition()
    {
        return $this->resourceAdapter->describeTable($this->resourceUnitName);
    }

    /**
     * Setter for current position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Getter for current position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Setter for resource unit name
     *
     * @param string $resourceName
     * @return $this
     */
    public function setResourceUnitName($resourceName)
    {
        $this->resourceUnitName = $resourceName;
        return $this;
    }

    /**
     * Returns configuration data for resource initialization
     *
     * @param \Migration\Config $configReader
     * @return array
     */
    abstract protected function getResourceConfig(\Migration\Config $configReader);
}
