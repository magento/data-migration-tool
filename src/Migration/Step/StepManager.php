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
            if ($this->progress->getResult(get_class($step), 'integrity') != true) {
                $this->logger->info(get_class($step) . ': Integrity');
                $integritySuccess = $step->integrity();
                $this->progress->saveResult(get_class($step), 'integrity', $integritySuccess);
            }
        }
        if (!$integritySuccess) {
            return $this;
        }

        /** @var StepInterface $step */
        foreach ($steps as $index => $step) {
            if ($this->progress->getResult(get_class($step), 'run') != true) {
                $this->logger->info(get_class($step) . ': Run');
                $runSuccess = $step->run();
                $this->progress->saveResult(get_class($step), 'run', $runSuccess);
            }
            $volumeCheckSuccess = true;
            if ($this->progress->getResult(get_class($step), 'volume_check') != true) {
                $this->logger->info(get_class($step) . ': Volume Check');
                $volumeCheckSuccess = $step->volumeCheck();
                $this->progress->saveResult(get_class($step), 'volume_check', $volumeCheckSuccess);
            }
        }
        if (!$volumeCheckSuccess) {
            return $this;
        }
        $this->logger->info(PHP_EOL . "Migration completed");
        return $this;
    }
}
