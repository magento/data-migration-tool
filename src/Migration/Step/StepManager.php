<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;

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
     * @var ProgressStep
     */
    protected $progress;

    /**
     * @param ProgressStep $progress
     * @param Logger $logger
     * @param StepFactory $factory
     */
    public function __construct(ProgressStep $progress, Logger $logger, StepFactory $factory)
    {
        $this->progress = $progress;
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * Run steps
     *
     * @return $this
     */
    public function runSteps()
    {
        $steps = $this->factory->getSteps();
        $integritySuccess = true;
        /** @var StepInterface $step */
        foreach ($steps as $index => $step) {
            $this->logger->info(PHP_EOL . $step->getTitle() . 'Integrity');
            if ($this->progress->getResult($step, 'integrity') != true) {
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
        foreach ($steps as $index => $step) {
            $this->logger->info(PHP_EOL . $step->getTitle() . 'Run');
            if ($this->progress->getResult($step, 'run') != true) {
                $runSuccess = $step->run();
                $this->progress->saveResult($step, 'run', $runSuccess);
            } else {
                $this->logger->info('Migration step completed');
            }
            $volumeCheckSuccess = true;
            $this->logger->info(PHP_EOL . $step->getTitle() . 'Volume Check');
            if ($this->progress->getResult($step, 'volume_check') != true) {
                $volumeCheckSuccess = $step->volumeCheck();
                $this->progress->saveResult($step, 'volume_check', $volumeCheckSuccess);
            } else {
                $this->logger->info('Volume check completed');
            }
        }
        if (!$volumeCheckSuccess) {
            return $this;
        }
        $this->logger->info(PHP_EOL . "Migration completed");
        return $this;
    }
}
