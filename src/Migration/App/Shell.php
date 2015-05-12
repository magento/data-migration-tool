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
class Shell extends ShellAbstract
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
     * @var Progress
     */
    protected $progress;

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var array
     */
    public $generalOptions = ['verbose', 'reset'];

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Migration\Config $config
     * @param Mode\ModeFactory $modeFactory
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Logger\Manager $logManager
     * @param Progress $progress
     * @param string $entryPoint
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Migration\Config $config,
        Mode\ModeFactory $modeFactory,
        \Migration\Logger\Logger $logger,
        \Migration\Logger\Manager $logManager,
        Progress $progress,
        $entryPoint
    ) {
        $this->logger = $logger;
        $this->logManager = $logManager;
        $this->modeFactory = $modeFactory;
        $this->progress = $progress;
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

            if (!$this->optionConfig()) {
                echo $this->getUsageHelp();
                return $this;
            }

            $this->processGeneralOptions();

            $mode = $this->createMode($modeName);
            $mode->run();
        } catch (Exception $e) {
            $this->logger->error('Migration tool exception: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Application failed with exception: ' . $e->getMessage());
            $this->logger->error($e->getTraceAsString());
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
  {$this->_entryPoint} <mode> --config=path/to/config.xml [options]
  {$this->_entryPoint} --help

Possible modes:
  settings            Migrate system configuration
  data                Main migration of data
  delta               Migrate the data is added into Magento after the main migration

Available options:
  --reset             Reset the current position of migration to start from the beginning
  --config <value>    Path to main configuration file, i.e.: etc/m1_version/config.xml
  --verbose <level>   Verbosity levels: DEBUG, INFO, ERROR
  --help              Help information

USAGE;
    }

    /**
     * @return string
     */
    protected function getMode()
    {
        $mode = array_keys($this->_args);
        $modeName = array_shift($mode);
        if ($modeName == 'migrate') {
            $modeName = array_shift($mode);
        }
        return $modeName;
    }

    /**
     * @param string $modeName
     * @return Mode\ModeInterface
     * @throws Exception
     */
    protected function createMode($modeName)
    {
        return $this->modeFactory->create($modeName);
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
     * @return bool
     */
    protected function optionConfig()
    {
        $config = $this->getArg('config');
        if ($config) {
            $this->config->init($this->getArg('config'));
        }

        return $config ? true : false;
    }

    /**
     * @return void
     */
    protected function optionReset()
    {
        if ($this->getArg('reset') === true) {
            $this->logger->info('Reset the current position of migration to start from the beginning');
            $this->progress->reset();
        }
    }
}
