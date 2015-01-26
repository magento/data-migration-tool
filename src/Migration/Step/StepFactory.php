<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use \Magento\Framework\ObjectManagerInterface;

/**
 * Class StepFactory
 */
class StepFactory
{
    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $steps;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Migration\Config $config
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(\Migration\Config $config, ObjectManagerInterface $objectManager)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSteps()
    {
        if (is_null($this->steps)) {
            $this->steps = [];
            foreach ($this->config->getSteps() as $stepClass) {
                $this->steps[] = $this->create($stepClass);
            }
        }

        return $this->steps;
    }

    /**
     * @param $stepClass
     * @return mixed
     * @throws \Exception
     */
    public function create($stepClass)
    {
        $step = $this->objectManager->create($stepClass);
        if (!($step instanceof StepInterface)) {
            throw new \Exception("Class: $stepClass must implement StepInterface.");
        }

        return $step;
    }
}
