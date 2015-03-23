<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Exception;
use Migration\Config;

/**
 * Class Manager
 */
class Manager
{
    /**
     * @param ModeFactory $modeFactory
     * @param Config $config
     * @param Factory $stepFactory
     */
    public function __construct(
        ModeFactory $modeFactory,
        Factory $stepFactory,
        Config $config
    ) {
        $this->modeFactory = $modeFactory;
        $this->stepFactory = $stepFactory;
        $this->config = $config;
    }

    /**
     * Run steps for specific mode
     *
     * @param string $mode
     * @return $this
     * @throws Exception
     */
    public function runSteps($mode)
    {
        $stepClasses = $this->config->getSteps($mode);
        $steps = [];
        foreach ($stepClasses as $stepClass) {
            /** @var StepInterface $step */
            $steps[] = $this->stepFactory->create($stepClass);
        }
        $mode = $this->modeFactory->create($mode);
        $mode->run($steps);
        return $this;
    }
}
