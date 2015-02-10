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
        foreach ($steps as $index => $step) {
            $this->logger->info(sprintf('Step %s of %s', $index + 1, count($steps)));
            /** @var StepInterface $step */
            $step->run();
        }
        $this->logger->info(PHP_EOL . "Migration completed");
        return $this;
    }
}
