<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

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
     * @var \Migration\Step\StepManager
     */
    protected $stepManager;

    /**
     * @var \Migration\Step\ProgressStep
     */
    protected $progressStep;

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Migration\Config $config
     * @param \Migration\Step\StepManager $stepManager
     * @param \Migration\Logger\Logger $logger
     * @param \Migration\Logger\Manager $logManager
     * @param \Migration\Step\ProgressStep $progressStep
     * @param string $entryPoint
     * @throws \Exception
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Migration\Config $config,
        \Migration\Step\StepManager $stepManager,
        \Migration\Logger\Logger $logger,
        \Migration\Logger\Manager $logManager,
        \Migration\Step\ProgressStep $progressStep,
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
        if ($this->_showHelp()) {
            return $this;
        }

        try {
            $verbose = $this->getArg('verbose');
            if (!empty($verbose)) {
                $this->logManager->process($verbose);
            } else {
                $this->logManager->process();
            }

            if ($this->getArg('config')) {
                $this->logger->info('Loaded custom config file: ' . $this->getArg('config'));
                $this->config->init($this->getArg('config'));
            } else {
                $this->logger->info('Loaded default config file');
                $this->config->init();
            }

            if ($this->getArg('type')) {
                $this->logger->info($this->getArg('type'));
            }
            $reset = $this->getArg('reset');
            if ($reset)
            {
                $this->logger->info('Current progress will be removed');
                $this->progressStep->clearLockFile();
            }

            $this->stepManager->runSteps();
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
Usage:  php -f {$this->_entryPoint} -- [options]

  --config <value>    Path to main configuration file
  --type <value>      Type of operation: migration or delta delivery
  --verbose <level>   Verbosity levels: DEBUG, INFO, NONE
  --force             Remove steps progress
  help              This help

USAGE;
    }
}
