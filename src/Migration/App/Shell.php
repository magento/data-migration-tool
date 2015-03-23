<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Migration\Exception;

/**
 * Class Shell
 */
class Shell extends \Magento\Framework\App\AbstractShell
{
    /**
     * @var \Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Migration\Logger\Manager
     */
    protected $logManager;

    /**
     * @var Step\Manager
     */
    protected $stepManager;

    /**
     * @var Step\Progress
     */
    protected $progressStep;

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var array
     */
    public $availableCommands = ['migration', 'delta', 'settings', 'reset', 'help'];

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Migration\Config $config
     * @param Step\Manager $stepManager
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Logger\Manager $logManager
     * @param Step\Progress $progressStep
     * @param string $entryPoint
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Migration\Config $config,
        Step\Manager $stepManager,
        \Migration\Logger\Logger $logger,
        \Migration\Logger\Manager $logManager,
        Step\Progress $progressStep,
        $entryPoint
    ) {
        $this->logger = $logger;
        $this->logManager = $logManager;
        $this->stepManager = $stepManager;
        $this->progressStep = $progressStep;
        parent::__construct($filesystem, $entryPoint);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $command = array_keys($this->_args);
            $command = array_shift($command);
            if (!$command || $command == 'help' || !in_array($command, $this->availableCommands)) {
                echo $this->getUsageHelp();
                return $this;
            }

            $verbose = $this->getArg('verbose');
            if (!empty($verbose)) {
                $this->logManager->process($verbose);
            } else {
                $this->logManager->process();
            }

            $config = $this->getArg('config');
            if ($config) {
                $this->logger->info('Loaded custom config file: ' . $config);
                $this->config->init($this->getArg('config'));
            } else {
                $this->logger->info('Loaded default config file');
                $this->config->init();
            }

            switch ($command) {
                case 'reset':
                    $this->logger->info('Current progress will be removed');
                    $this->progressStep->clearLockFile();
                    break;
                case 'migration':
                case 'delta':
                case 'settings':
                    $this->logger->info('Running mode: ' . $command);
                    $this->stepManager->runSteps($command);
                    break;

            }
        } catch (Exception $e) {
            $this->logger->error('Migration tool exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Application failed with exception: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageHelp()
    {
        return <<<USAGE
Usage: {$this->_entryPoint} <command> [options]

  Available commands:
  migration           Main migration of data
  delta               Migrate the data had been added into Magento after the base migration
  settings            Migrate system configuration
  reset               Reset the current position of migration to start from the beginning
  help                Help information

  Available options:
  --config <value>    Path to main configuration file
  --verbose <level>   Verbosity levels: DEBUG, INFO, NONE

USAGE;
    }
}
