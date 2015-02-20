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
     * @param Logger $logger
     * @param StepFactory $factory
     * @param Config $config
     */
    public function __construct(Logger $logger, StepFactory $factory, Config $config)
    {
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
        foreach ($steps as $index => $stepClass) {
            $step = $this->factory->create($stepClass);
            $this->logger->info(sprintf('Step %s of %s', $index + 1, count($steps)));
            /** @var StepInterface $step */
            $step->run();
        }
        $this->logger->info(PHP_EOL . "Migration completed");
        return $this;
    }
}
