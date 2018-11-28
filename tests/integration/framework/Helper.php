<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\TestFramework;

/**
 * Helper for preparing databases, initialize ObjectManager
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    protected $dbFixturePath;

    /**
     * @var string
     */
    public $configPath;

    /**
     * @var string
     */
    protected $testSuite;

    /**
     * @var array
     */
    protected $testFixtures;

    /**
     * @var string
     */
    protected $currentFixture;

    /**
     * @param \Magento\Framework\Shell $shell
     * @param string $magentoDir
     * @param string $dbFixturePath
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\Shell $shell,
        $magentoDir,
        $dbFixturePath
    ) {
        $this->shell = $shell;
        $this->magentoDir = $magentoDir;
        $this->dbFixturePath = $dbFixturePath;
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
            $dbFixturePath = __DIR__ . '/../resource/';
            self::$instance = new Helper(
                $shell,
                $magentoDir,
                $dbFixturePath
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
        $this->objectManager->configure([
            'preferences' => [
                \Migration\App\ProgressBar\LogLevelProcessor::class => \Migration\TestFramework\ProgressBar::class
            ],
            \Migration\Logger\Logger::class => [
                'arguments' => [
                    'handlers' => [
                        'quiet' => [
                            'instance' => \Migration\TestFramework\QuietLogHandler::class
                        ]
                    ]
                ]
            ],
            \Migration\ResourceModel\Source::class => ['shared' => false],
            \Migration\ResourceModel\Destination::class => ['shared' => false],
        ]);
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
        $configFilePool = new \Magento\Framework\Config\File\ConfigFilePool;
        return (new \Magento\Framework\App\ObjectManagerFactory($dirList, $driverPool, $configFilePool))->create([]);
    }

    /**
     * Reinstall Db for source and destination
     *
     * @param string $fixturePath
     * @throws \Exception
     * @return void
     */
    protected function reinstallDb($fixturePath)
    {
        $mysqlConfigPath = dirname(__DIR__) . '/etc/mysql.php';
        if (!is_file($mysqlConfigPath)) {
            throw new \Exception('Database configuration file does not exist: ' . $mysqlConfigPath);
        }
        $resourceSource = $fixturePath . '/source.sql';
        $resourceDestination = $fixturePath . '/dest.sql';
        if (file_exists($this->dbFixturePath . $fixturePath)) {
            $resourceSource = $this->dbFixturePath . $fixturePath . '/source.sql';
            $resourceDestination = $this->dbFixturePath . $fixturePath . '/dest.sql';
        } elseif (!file_exists($fixturePath)) {
            throw new \Exception('Database fixture not found: ' . $fixturePath);
        }
        $config = include $mysqlConfigPath;
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
                $resourceSource
            ]
        );
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
                $resourceDestination
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

    /**
     * @return string
     */
    public function getTestSuite()
    {
        return $this->testSuite;
    }

    /**
     * @param string $testSuite
     * @return $this
     */
    public function setTestSuite($testSuite)
    {
        $this->testSuite = $testSuite;
        return $this;
    }

    /**
     * @param array $annotations
     * @return void
     */
    public function loadFixture($annotations)
    {
        $fixture = 'default';
        $annotations = array_replace($annotations['class'], $annotations['method']);
        if (!empty($annotations['dbFixture'])) {
            $fixtureName = $this->getFixturePrefix() . reset($annotations['dbFixture']);
            $fixture = (is_dir($this->dbFixturePath . $fixtureName))
                ? $fixtureName
                : reset($annotations['dbFixture']);
        }
        if (!isset($this->testFixtures[$this->getTestSuite()]) || $this->currentFixture != $fixture) {
            $this->reinstallDb($fixture);
            $this->testFixtures[$this->getTestSuite()] = $fixture;
            $this->currentFixture = $fixture;
        }
    }

    /**
     * Check if fixture prefix defined and return it
     *
     * @return string
     */
    public function getFixturePrefix()
    {
        $prefix = null;
        if (defined('FIXTURE_PREFIX')) {
            $prefix = FIXTURE_PREFIX;
        }
        return (string)$prefix;
    }
}
