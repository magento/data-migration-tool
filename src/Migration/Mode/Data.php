<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\SetupChangeLog;
use Migration\App\Mode\StepList;
use Migration\App\Step\Progress;
use Migration\App\Step\RollbackInterface;
use Migration\Logger\Logger;
use Migration\Exception;

/**
 * Class Migration
 */
class Data implements \Migration\App\Mode\ModeInterface
{
    /**
     * @var \Migration\App\Mode\StepList
     */
    protected $stepList;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Progress
     */
    protected $progress;

    /**
     * @var SetupChangeLog
     */
    protected $setupChangeLog;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepList $stepList
     * @param SetupChangeLog $setupChangeLog
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        StepList $stepList,
        SetupChangeLog $setupChangeLog
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepList = $stepList;
        $this->setupChangeLog = $setupChangeLog;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageHelp()
    {
        return <<<USAGE

Data migration mode usage information:

Main data migration
USAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $result = true;
        $steps = $this->stepList->getSteps('data');
        foreach ($steps as $step) {
            $result = $result && $this->runStage($step, 'integrity', 'integrity check');
            if (!$result) {
                throw new Exception('Integrity Check failed');
            }
        }

        $result = $this->runStage($this->setupChangeLog, 'setupChangeLog', 'setup triggers');
        if (!$result) {
            throw new Exception('Setup triggers failed');
        }

        foreach ($steps as $step) {
            $result = $this->runStage($step, 'run', 'data migration');
            if (!$result) {
                $this->rollback($step);
                throw new Exception('Data Migration failed');
            }
            $result = $this->runStage($step, 'volumeCheck', 'volume check');
            if (!$result) {
                $this->rollback($step);
                throw new Exception('Volume Check failed');
            }
        }

        $this->logger->info(PHP_EOL . "Migration completed");
        return true;
    }

    /**
     * @param mixed $object
     * @param string $method
     * @param string $stage
     * @return bool
     */
    protected function runStage($object, $method, $stage)
    {
        $title = method_exists($object, 'getTitle') ? $object->getTitle() : 'Stage';

        $this->logger->info(sprintf('%s: %s', PHP_EOL . $title, $stage));

        if ($this->progress->isCompleted($object, $stage)) {
            return true;
        }

        try {
            $result = call_user_func([$object, $method]);
        } catch (\Exception $e) {
            $this->logger->error(PHP_EOL . $e->getMessage());
            return false;
        }

        if ($result) {
            $this->progress->saveResult($object, $stage, $result);
        }

        return $result;
    }

    /**
     * @param mixed $step
     * @return void
     */
    protected function rollback($step)
    {
        if ($step instanceof RollbackInterface) {
            $this->logger->info(PHP_EOL . 'Error occurred. Rollback.');
            $this->runStage($step, 'rollback', 'rollback');
            $this->progress->reset($step);
            $this->logger->info(PHP_EOL . 'Please fix errors and run Migration Tool again');
        }
    }
}
