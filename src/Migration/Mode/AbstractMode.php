<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var string
     */
    protected $autoResolveMessage = 'You can use --auto or -a option to ignore not mapped differences'
        . ' between source and destination to continue migration';

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
     * Run stage
     *
     * @param StageInterface $object
     * @param string $step
     * @param string $stage
     * @param boolean $force
     * @return bool
     */
    protected function runStage($object, $step, $stage, $force = false)
    {
        $this->logger->info(
            'started',
            ['step' => $step, 'stage' => $stage, 'mode' => $this->mode]
        );
        if ($this->progress->isCompleted($object, $stage) && !$force) {
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
