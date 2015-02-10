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
     * @var bool
     */
    protected $doCleanup;

    /**
     * @param \Magento\Framework\Shell $shell
     * @param $magentoDir
     * @param $dbDumpSourcePath
     * @param $dbDumpDestinationPath
     * @throws \Exception
     */
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
        $this->doCleanup = defined(CLEANUP_DATABASE) ? CLEANUP_DATABASE : true;
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
            $dbDumpSourcePath = __DIR__ . '/../' . DB_DUMP_SOURCE;
            $dbDumpDestinationPath = __DIR__ . '/../' . DB_DUMP_DESTINATION;
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
     * @throws \Exception
     * @throws \Magento\Framework\Exception
     */
    protected function reinstallDb()
    {
        $mysqlConfigPath = dirname(__DIR__) . '/etc/mysql.php';
        if (!is_file($mysqlConfigPath)) {
            throw new \Exception('Database configuration file does not exists: ' . $mysqlConfigPath);
        }
        $config = include $mysqlConfigPath;
        if ($this->doCleanup) {
            $this->shell->execute(
                'mysql --host=%s --user=%s --password=%s -e %s',
                [
                    $config['source_db_host'],
                    $config['source_db_user'],
                    $config['source_db_pass'],
                    "DROP DATABASE IF EXISTS `{$config['source_db_name']}`"
                ]
            );
            $this->shell->execute(
                'mysql --host=%s --user=%s --password=%s -e %s',
                [
                    $config['source_db_host'],
                    $config['source_db_user'],
                    $config['source_db_pass'],
                    "CREATE DATABASE IF NOT EXISTS `{$config['source_db_name']}`"
                ]
            );
            $this->shell->execute(
                'mysql --host=%s --user=%s --password=%s --database=%s < %s',
                [
                    $config['source_db_host'],
                    $config['source_db_user'],
                    $config['source_db_pass'],
                    $config['source_db_name'],
                    $this->dbDumpSourcePath
                ]
            );
        }
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $config['dest_db_host'],
                $config['dest_db_user'],
                $config['dest_db_pass'],
                "DROP DATABASE IF EXISTS `{$config['dest_db_name']}`"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s -e %s',
            [
                $config['dest_db_host'],
                $config['dest_db_user'],
                $config['dest_db_pass'],
                "CREATE DATABASE `{$config['dest_db_name']}`"
            ]
        );
        $this->shell->execute(
            'mysql --host=%s --user=%s --password=%s --database=%s < %s',
            [
                $config['dest_db_host'],
                $config['dest_db_user'],
                $config['dest_db_pass'],
                $config['dest_db_name'],
                $this->dbDumpDestinationPath
            ]
        );
    }

    /**
     * getter for config path
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }
}
