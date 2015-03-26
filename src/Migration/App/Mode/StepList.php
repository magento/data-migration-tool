<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

/**
 * Class AbstractMode
 */
class StepList
{
    /**
     * @var \Migration\App\Step\Factory
     */
    protected $stepFactory;

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @param \Migration\App\Step\Factory $stepFactory
     * @param \Migration\Config $config
     */
    public function __construct(
        \Migration\App\Step\Factory $stepFactory,
        \Migration\Config $config
    ) {
        $this->stepFactory = $stepFactory;
        $this->config = $config;
    }

    /**
     * @param string $modeName
     * @return array
     * @throws \Migration\Exception
     */
    public function getSteps($modeName)
    {
        $stepClasses = $this->config->getSteps($modeName);
        $steps = [];
        foreach ($stepClasses as $stepClass) {
            $steps[] = $this->stepFactory->create($stepClass);
        }
        return $steps;
    }
}
