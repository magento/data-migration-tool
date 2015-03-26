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
     * @var Mode\ModeFactory
     */
    protected $modeFactory;

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
    public $generalOptions = ['verbose', 'config', 'reset'];

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Migration\Config $config
     * @param Mode\ModeFactory $modeFactory
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Logger\Manager $logManager
     * @param Step\Progress $progressStep
     * @param string $entryPoint
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Migration\Config $config,
        Mode\ModeFactory $modeFactory,
        \Migration\Logger\Logger $logger,
        \Migration\Logger\Manager $logManager,
        Step\Progress $progressStep,
        $entryPoint
    ) {
        $this->logger = $logger;
        $this->logManager = $logManager;
        $this->modeFactory = $modeFactory;
        $this->progressStep = $progressStep;
        parent::__construct($filesystem, $entryPoint);
        $this->config = $config;
        set_time_limit(0);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        try {
            $modeName = $this->getMode();
            if ($this->_showHelp()) {
                if (!empty($modeName) && $modeName != 'help') {
                    $mode = $this->createMode($modeName);
                    echo $mode->getUsageHelp();
                }
                return $this;
            }

            if (empty($modeName)) {
                echo $this->getUsageHelp();
                return $this;
            }

            $this->processGeneralOptions();

            $mode = $this->createMode($modeName);
            $this->logger->info('Running mode: ' . $modeName);

            $mode->run();
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
Usage:
  {$this->_entryPoint} <mode> [options]
  {$this->_entryPoint} --help

Possible modes:
  data                Main migration of data
  delta               Migrate the data had been added into Magento after the base migration
  settings            Migrate system configuration

Available options:
  --reset             Reset the current position of migration to start from the beginning
  --config <value>    Path to main configuration file
  --verbose <level>   Verbosity levels: DEBUG, INFO, NONE
  --help              Help information

USAGE;
    }

    /**
     * @return string
     */
    protected function getMode()
    {
        $mode = array_keys($this->_args);
        $mode = array_shift($mode);
        return $mode;
    }

    /**
     * @param string $modeName
     * @return Mode\ModeInterface
     * @throws Exception
     */
    protected function createMode($modeName)
    {
        try {
            return $this->modeFactory->create($modeName);
        } catch (\Exception $e) {
            throw new Exception("Mode '$modeName' does not exists");
        }
    }

    /**
     * @return void
     */
    protected function processGeneralOptions()
    {
        foreach ($this->generalOptions as $option) {
            $optionMethod = 'option' . ucfirst($option);
            if (method_exists($this, $optionMethod)) {
                call_user_func([$this, $optionMethod]);
            }
        }
    }

    /**
     * @return void
     */
    protected function optionVerbose()
    {
        $verbose = $this->getArg('verbose');
        if (!empty($verbose)) {
            $this->logManager->process($verbose);
        } else {
            $this->logManager->process();
        }
    }

    /**
     * @return void
     */
    protected function optionConfig()
    {
        $config = $this->getArg('config');
        if ($config) {
            $this->logger->info('Loaded custom config file: ' . $config);
            $this->config->init($this->getArg('config'));
        } else {
            $this->logger->info('Loaded default config file');
            $this->config->init();
        }
    }

    /**
     * @return void
     */
    protected function optionReset()
    {
        if ($this->getArg('reset') === true) {
            $this->logger->info('Reset the current position of migration to start from the beginning');
            $this->progressStep->clearLockFile();
        }
    }
}
