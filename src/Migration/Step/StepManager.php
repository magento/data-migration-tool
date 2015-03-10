<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\Config;

/**
 * Class StepManager
 */
class StepManager
{
    /**
     * @var StepFactory
     */
    protected $factory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProgressStep
     */
    protected $progress;

    /**
     * @param ProgressStep $progress
     * @param Logger $logger
     * @param StepFactory $factory
     * @param Config $config
     */
    public function __construct(ProgressStep $progress, Logger $logger, StepFactory $factory, Config $config)
    {
        $this->progress = $progress;
        $this->factory = $factory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Run steps
     *
     * @return $this
     * @throws \Exception
     */
    public function runSteps()
    {
        $steps = $this->config->getSteps();
        $stepInstances = [];
        foreach ($steps as $stepConfig) {
            /** @var StepInterface $step */
            $step = $this->factory->create($stepConfig['class']);
            $this->logger->info(PHP_EOL . $step->getTitle() . ': integrity check');
            if ($this->progress->isCompleted($step, 'integrity') != true) {
                $integritySuccess = $step->integrity();
                $this->progress->saveResult($step, 'integrity', $integritySuccess);
                if (!$integritySuccess) {
                    return $this;
                }
                if ($stepConfig['solid']) {
                    $this->runSolidStep($step);
                }
            } else {
                $this->logger->info('Integrity check completed');
            }
            if (!$stepConfig['solid']) {
                $stepInstances[] = $step;
            }
        }

        /** @var StepInterface $step */
        foreach ($stepInstances as $step) {
            $this->logger->info(PHP_EOL . $step->getTitle() . ': run');
            if ($this->progress->isCompleted($step, 'run') != true) {
                $this->progress->saveResult($step, 'run', $step->run());
            } else {
                $this->logger->info('Migration stage completed');
            }
            $this->logger->info(PHP_EOL . $step->getTitle() . ': volume check');
            if ($this->progress->isCompleted($step, 'volume_check') != true) {
                $volumeCheckSuccess = $step->volumeCheck();
                $this->progress->saveResult($step, 'volume_check', $volumeCheckSuccess);
                if (!$volumeCheckSuccess) {
                    return $this;
                }
            } else {
                $this->logger->info('Volume check completed');
            }
        }
        $this->logger->info(PHP_EOL . "Migration completed");
        $this->progress->clearLockFile();
        return $this;
    }

    /**
     * @param StepInterface $step
     * @return bool
     */
    protected function runSolidStep($step)
    {
        $this->logger->info(PHP_EOL . $step->getTitle() . ': run');
        $this->progress->saveResult($step, 'run', $step->run());
        $this->logger->info(PHP_EOL . $step->getTitle() . ': volume check');
        $volumeCheckSuccess = $step->volumeCheck();
        $this->progress->saveResult($step, 'volume_check', $volumeCheckSuccess);
        if (!$volumeCheckSuccess) {
            return false;
        }
        return true;
    }
}
