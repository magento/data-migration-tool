<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\TestFramework;

/**
 *
 */
class Helper
{
    protected static $instance;

    protected $objectManager;

    protected $shell;

    protected $magentoDir;

    public function __construct(
        \Magento\Framework\Shell $shell,
        $magentoDir,
        $dbDumpSourcePath,
        $dbDumpDestinationPath
    ) {
        $this->shell = $shell;
        $this->magentoDir = $magentoDir;
        $this->dbDumpSourcePath = $dbDumpSourcePath;
        $this->dbDumpDestinationPath = $dbDumpDestinationPath;
        $this->reinstallDb();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer());
            $magentoDir = require __DIR__ . '/../../../etc/magento_path.php';
            $dbDumpSourcePath = __DIR__ . '/../etc/' . DB_DUMP_SOURCE;
            $dbDumpDestinationPath = __DIR__ . '/../etc/' . DB_DUMP_DESTINATION;
            self::$instance = new Helper(
                $shell,
                $magentoDir,
                $dbDumpSourcePath,
                $dbDumpDestinationPath
            );
        }
        return self::$instance;
    }

    /**
     * @return \Magento\Framework\ObjectManagerInterface
     */
    public function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->initObjectManager();
        }
        return $this->objectManager;
    }

    /**
     * Create ObjectManager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function initObjectManager()
    {
        $dirList = new \Magento\Framework\App\Filesystem\DirectoryList($this->magentoDir);
        $driverPool = new \Magento\Framework\Filesystem\DriverPool;
        return (new \Magento\Framework\App\ObjectManagerFactory($dirList, $driverPool))->create([]);
    }

    protected function reinstallDb()
    {
        /** @var \Migration\Config $configReader */
        $configReader  = $this->getObjectManager()->get('\Migration\Config')->init();
        $source = $configReader->getSource();
        $destination = $configReader->getDestination();
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $source['database']['host'],
                $source['database']['user'],
                $source['database']['password'],
                "DROP DATABASE IF EXISTS `{$source['database']['name']}`;
                    CREATE DATABASE IF NOT EXISTS `{$source['database']['name']}`;"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $destination['database']['host'],
                $destination['database']['user'],
                $destination['database']['password'],
                "DROP DATABASE IF EXISTS `{$destination['database']['name']}`;
                    CREATE DATABASE IF NOT EXISTS `{$destination['database']['name']}`;"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s --database=%s < %s',
            [
                $source['database']['host'],
                $source['database']['user'],
                $source['database']['password'],
                $source['database']['name'],
                $this->dbDumpSourcePath
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s --database=%s < %s',
            [
                $destination['database']['host'],
                $destination['database']['user'],
                $destination['database']['password'],
                $destination['database']['name'],
                $this->dbDumpDestinationPath
            ]
        );
    }
}
