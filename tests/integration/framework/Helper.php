<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\TestFramework;

/**
 * Helper for preparing databases, initialize ObjectManager
 */
class Helper
{
    /**
     * @var \Migration\TestFramework\Helper
     */
    protected static $instance;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Shell
     */
    protected $shell;

    /**
     * @var string
     */
    protected $magentoDir;

    /**
     * @var string
     */
    protected $dbDumpSourcePath;

    /**
     * @var string
     */
    protected $dbDumpDestinationPath;

    /**
     * @var string
     */
    public $configPath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Shell $shell
     * @param string $magentoDir
     * @param string $dbDumpSourcePath
     * @param string $dbDumpDestinationPath
     * @param string $configPath
     */
    public function __construct(
        \Magento\Framework\Shell $shell,
        $magentoDir,
        $dbDumpSourcePath,
        $dbDumpDestinationPath,
        $configPath
    ) {
        $this->shell = $shell;
        $this->magentoDir = $magentoDir;
        $this->dbDumpSourcePath = $dbDumpSourcePath;
        $this->dbDumpDestinationPath = $dbDumpDestinationPath;
        $this->configPath = $configPath;
        $this->reinstallDb();
    }

    /**
     * Initializes and returns singleton instance of this class
     *
     * @return Helper
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            $shell = new \Magento\Framework\Shell(new \Magento\Framework\Shell\CommandRenderer());
            $magentoDir = require __DIR__ . '/../../../etc/magento_path.php';
            $dbDumpSourcePath = __DIR__ . '/../etc/' . DB_DUMP_SOURCE;
            $dbDumpDestinationPath = __DIR__ . '/../etc/' . DB_DUMP_DESTINATION;
            $configPath = __DIR__ . '/../etc/config.xml';
            self::$instance = new Helper(
                $shell,
                $magentoDir,
                $dbDumpSourcePath,
                $dbDumpDestinationPath,
                $configPath
            );
        }
        return self::$instance;
    }

    /**
     * Getter for ObjectManager
     *
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
     * Init ObjectManager
     *
     * @return \Magento\Framework\ObjectManagerInterface
     */
    protected function initObjectManager()
    {
        $dirList = new \Magento\Framework\App\Filesystem\DirectoryList($this->magentoDir);
        $driverPool = new \Magento\Framework\Filesystem\DriverPool;
        return (new \Magento\Framework\App\ObjectManagerFactory($dirList, $driverPool))->create([]);
    }

    /**
     * Reinstall Db for source and destination
     *
     * @throws \Magento\Framework\Exception
     */
    protected function reinstallDb()
    {
        /** @var \Migration\Config $configReader */
        $configReader  = $this->getObjectManager()->get('\Migration\Config')->init($this->configPath);
        $source = $configReader->getSource();
        $destination = $configReader->getDestination();
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $source['database']['host'],
                $source['database']['user'],
                $source['database']['password'],
                "DROP DATABASE IF EXISTS `{$source['database']['name']}`"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $source['database']['host'],
                $source['database']['user'],
                $source['database']['password'],
                "CREATE DATABASE IF NOT EXISTS `{$source['database']['name']}`"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $destination['database']['host'],
                $destination['database']['user'],
                $destination['database']['password'],
                "DROP DATABASE IF EXISTS `{$destination['database']['name']}`"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $destination['database']['host'],
                $destination['database']['user'],
                $destination['database']['password'],
                "CREATE DATABASE `{$destination['database']['name']}`"
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

    /**
     * getter for config path
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }
}
