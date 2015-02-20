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
     * @param Logger $logger
     * @param StepFactory $factory
     */
    public function __construct(Logger $logger, StepFactory $factory)
    {
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
        $intergitySuccess = true;
        /** @var StepInterface $step */
        foreach ($steps as $index => $step) {
            $result = $step->integrity();
            if (!$result) {
                $intergitySuccess = false;
            }
        }
        if (!$intergitySuccess) {
            return $this;
        }

        /** @var StepInterface $step */
        foreach ($steps as $index => $step) {
            $step->run();
            $result = $step->volumeCheck();
            if (!$result) {
                return $this;
            }
        }
        $this->logger->info(PHP_EOL . "Migration completed");
        return $this;
    }
}
