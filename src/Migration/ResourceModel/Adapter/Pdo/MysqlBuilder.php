<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Adapter\Pdo;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql as PdoMysql;
use Magento\Framework\DB\SelectFactory;
use Migration\Config;

/**
 * Builder class for @see PdoMysql
 */
class MysqlBuilder
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
        $instanceName = '\\Magento\\Framework\\DB\\Adapter\\Pdo\\Mysql'
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $resourceType
     * @return PdoMysql
     */
    public function build($resourceType)
    {
        $instance = $this->objectManager->create(
            $this->instanceName,
            [
                'config' => $this->getConfig($resourceType),
                'selectFactory' => $this->getSelectFactory()
            ]
        );
        $instance->disallowDdlCache();
        $this->runInitStatements($instance, $resourceType);
        return $instance;
    }

    /**
     * Returns well-formed configuration array of $resourceType resource for @see PdoMysql
     *
     * @param string $resourceType
     * @return array
     */
    private function getConfig($resourceType)
    {
        $resource = $this->config->getResourceConfig($resourceType);
        $type = $resource['type'];
        $config['host'] = $resource[$type]['host'];
        $config['dbname'] = $resource[$type]['name'];
        $config['username'] = $resource[$type]['user'];
        $config['password'] = !empty($resource[$type]['password']) ? $resource[$type]['password'] : '';
        if (!empty($resource[$type]['port'])) {
            $config['port'] = $resource[$type]['port'];
        }
        return $config;
    }

    /**
     * Run init SQL statements
     *
     * @param \Magento\Framework\DB\Adapter\Pdo\Mysql $instance
     * @param string $resourceType
     * @return void
     */
    private function runInitStatements(PdoMysql $instance, $resourceType)
    {
        $initStatements = $this->config->getOption('init_statements_' . $resourceType);
        if (!empty($initStatements)) {
            $instance->query($initStatements);
        }
    }

    /**
     * @return SelectFactory
     */
    private function getSelectFactory()
    {
        $parts = [];
        $editionMigrate = $this->config->getOption('edition_migrate');
        if (in_array($editionMigrate, [Config::EDITION_MIGRATE_CE_TO_EE, Config::EDITION_MIGRATE_EE_TO_EE])) {
            $parts['disable_staging_preview'] = true;
        }
        return $this->objectManager->create('\\Magento\\Framework\\DB\\SelectFactory', ['parts' => $parts]);
    }
}
