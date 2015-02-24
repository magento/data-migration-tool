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
     */
    public function runSteps()
    {
        $steps = $this->config->getSteps();
        $integritySuccess = true;
        $stepInstances = [];
        foreach ($steps as $stepClass) {
            /** @var StepInterface $step */
            $step = $this->factory->create($stepClass);
            $this->logger->info(PHP_EOL . $step->getTitle() . ': integrity check');
            if ($this->progress->isCompleted($step, 'integrity') != true) {
                $stepInstances[] = $step;
                $integritySuccess = $step->integrity();
                $this->progress->saveResult($step, 'integrity', $integritySuccess);
            } else {
                $this->logger->info('Integrity check completed');
            }
        }
        if (!$integritySuccess) {
            return $this;
        }

        /** @var StepInterface $step */
        foreach ($stepInstances as $step) {
            $this->logger->info(PHP_EOL . $step->getTitle() . ': run');
            if ($this->progress->isCompleted($step, 'run') != true) {
                $runSuccess = $step->run();
                $this->progress->saveResult($step, 'run', $runSuccess);
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
        return $this;
    }
}
