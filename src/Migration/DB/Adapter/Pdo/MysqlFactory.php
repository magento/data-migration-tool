<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\DB\Adapter\Pdo;

use Magento\Framework\ObjectManagerInterface;
use Migration\DB\Adapter\Pdo\Mysql as DatabaseAdapter;
use Migration\Config;

/**
 * Factory class for @see DatabaseAdapter
 */
class MysqlFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName = null;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        $instanceName = '\\Migration\\DB\\Adapter\\Pdo\\Mysql'
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $resourceType
     * @return DatabaseAdapter
     */
    public function create($resourceType)
    {
        $config = $this->getResourceConfig($resourceType);
        $this->config->getResourceConfig($resourceType);
        $instance = $this->objectManager->create($this->instanceName, ['config' => $config]);
        $instance->disallowDdlCache();
        return $instance;
    }

    /**
     * Returns well-formed configuration array of $resourceType resource for @see DatabaseAdapter
     *
     * @param string $resourceType
     * @return array
     */
    private function getResourceConfig($resourceType)
    {
        $resource = $this->config->getResourceConfig($resourceType);
        $type = $resource['type'];
        $config['database']['host'] = $resource[$type]['host'];
        $config['database']['dbname'] = $resource[$type]['name'];
        $config['database']['username'] = $resource[$type]['user'];
        $config['database']['password'] = !empty($resource[$type]['password']) ? $resource[$type]['password'] : '';
        $initStatements = $this->config->getOption('init_statements_' . $type);
        if (!empty($initStatements)) {
            $config['database']['initStatements'] = $initStatements;
        }
        $editionMigrate = $this->config->getOption('edition_migrate');
        if (in_array($editionMigrate, [Config::EDITION_MIGRATE_CE_TO_EE, Config::EDITION_MIGRATE_EE_TO_EE])) {
            $config['init_select_parts'] = ['disable_staging_preview' => true];
        }
        return $config;
    }
}
