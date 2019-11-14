<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

/**
 * Class AbstractMode
 */
class StepList
{
    /**
     * @var \Migration\App\Step\StageFactory
     */
    protected $stepFactory;

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @param \Migration\App\Step\StageFactory $stepFactory
     * @param \Migration\Config $config
     * @param string $mode
     */
    public function __construct(
        \Migration\App\Step\StageFactory $stepFactory,
        \Migration\Config $config,
        $mode
    ) {
        $this->stepFactory = $stepFactory;
        $this->config = $config;
        $this->mode = $mode;
    }

    /**
     * Create instances
     *
     * @return void
     */
    protected function createInstances()
    {
        array_walk_recursive($this->data, function (&$item, $key) {
            if (!is_array($item)) {
                $item = $this->stepFactory->create($item, ['stage' => $key, 'mode' => $this->mode]);
            }
        });
    }

    /**
     * Get steps
     *
     * @return array
     */
    public function getSteps()
    {
        if (empty($this->data)) {
            $this->data = $this->config->getSteps($this->mode);
            $this->createInstances();
        }
        return $this->data;
    }
}
