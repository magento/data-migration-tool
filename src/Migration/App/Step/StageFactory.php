<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Magento\Framework\ObjectManagerInterface;
use Migration\Exception;

/**
 * Class StageFactory
 */
class StageFactory
{
    /**
     * @var array
     */
    protected $steps;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create
     *
     * @param string $stageClass
     * @param array $arguments
     * @return StageInterface
     * @throws Exception
     */
    public function create($stageClass, array $arguments = [])
    {
        $step = $this->objectManager->create($stageClass, $arguments);
        if (!($step instanceof StageInterface)) {
            throw new Exception("Class: $stageClass must implement StageInterface.");
        }

        return $step;
    }
}
