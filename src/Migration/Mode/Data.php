<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Step\DeltaInterface;
use Migration\App\Step\Progress;
use Migration\App\Step\RollbackInterface;
use Migration\App\Step\StepInterface;
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
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepList $stepList
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepList $stepList
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepList = $stepList;
    }

    /**
     * {@inheritdoc}
     */
    public function helpUsage()
    {
        return <<<USAGE

Data mode usage information:

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
            $result = $result && $this->runStep($step, 'integrity check');
            if (!$result) {
                throw new Exception('Integrity Check failed');
            }
        }

        foreach ($steps as $step) {
            if ($step instanceof DeltaInterface) {
                $result = $this->runStep($step, 'setup triggers');
                if (!$result) {
                    throw new Exception('Setup triggers failed');
                }
            }
        }

        foreach ($steps as $step) {
            $result = $this->runStep($step, 'data migration');
            if (!$result) {
                $this->rollback($step);
                throw new Exception('Data Migration failed');
            }
            $result = $this->runStep($step, 'volume check');
            if (!$result) {
                $this->rollback($step);
                throw new Exception('Volume Check failed');
            }
        }

        $this->logger->info(PHP_EOL . "Migration completed");
        $this->progress->clearLockFile();
        return true;
    }

    /**
     * @param StepInterface $step
     * @param string $stepPart
     * @return bool
     */
    public function runStep(StepInterface $step, $stepPart)
    {
        $this->logger->info(sprintf('%s: %s', PHP_EOL . $step->getTitle(), $stepPart));

        if ($this->progress->isCompleted($step, $stepPart)) {
            return true;
        }

        $result = false;
        try {
            switch ($stepPart) {
                case 'integrity check':
                    $result = $step->integrity();
                    break;
                case 'data migration':
                    $result = $step->run();
                    break;
                case 'volume check':
                    $result = $step->volumeCheck();
                    break;
                case 'setup triggers':
                    $result = $step->setUpChangeLog();
                    break;
                case 'rollback':
                    $step->rollback();
                    $this->progress->resetStep($step);
                    $this->logger->info(PHP_EOL . 'Please fix errors and run Migration Tool again');
                    break;
            }
        } catch (\Exception $e) {
            $this->logger->error(PHP_EOL . $e->getMessage());
            return false;
        }

        if ($result) {
            $this->progress->saveResult($step, $stepPart, $result);
        }

        return $result;
    }

    /**
     * @param mixed $step
     */
    protected function rollback($step)
    {
        if ($step instanceof RollbackInterface) {
            $this->logger->info(PHP_EOL . 'Error occurred. Rollback.');
            $this->runStep($step, 'rollback');
        }
    }
}
