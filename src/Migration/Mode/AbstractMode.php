<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Progress;
use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;

/**
 * Abstract Mode
 */
abstract class AbstractMode
{
    /**
     * @var \Migration\App\Mode\StepListFactory
     */
    protected $stepListFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Progress
     */
    protected $progress;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var bool
     */
    protected $canBeCompleted = true;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepListFactory $stepListFactory
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepListFactory $stepListFactory
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepListFactory = $stepListFactory;
    }

    /**
     * @param StageInterface $object
     * @param string $step
     * @param string $stage
     * @return bool
     */
    protected function runStage($object, $step, $stage)
    {
        $this->logger->info(
            'started',
            ['step' => $step, 'stage' => $stage, 'mode' => $this->mode]
        );
        if ($this->progress->isCompleted($object, $stage)) {
            return true;
        }
        try {
            $result = $object->perform();
        } catch (\Migration\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        if ($result && $this->canBeCompleted) {
            $this->progress->saveResult($object, $stage, $result);
        }

        return $result;
    }
}
