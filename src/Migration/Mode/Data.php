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
use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Exception;

/**
 * Class Migration
 */
class Data implements \Migration\App\Mode\ModeInterface
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
     * @var SetupChangeLog
     */
    protected $setupChangeLog;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepListFactory $stepListFactory
     * @param SetupChangeLog $setupChangeLog
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepListFactory $stepListFactory,
        SetupChangeLog $setupChangeLog
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepListFactory = $stepListFactory;
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
        /** @var StepList $steps */
        $steps = $this->stepListFactory->create(['mode' => 'data']);
        foreach ($steps->getSteps() as $stepName => $step) {
            if (!empty($step['integrity'])) {
                $result = $this->runStage($step['integrity'], $stepName, 'integrity check') && $result;
            }
        }
        if (!$result) {
            throw new Exception('Integrity Check failed');
        }

        $result = $this->runStage($this->setupChangeLog, 'Stage', 'setup triggers');
        if (!$result) {
            throw new Exception('Setup triggers failed');
        }

        foreach ($steps->getSteps() as $stepName => $step) {
            if (empty($step['data'])) {
                continue;
            }
            $result = $this->runStage($step['data'], $stepName, 'data migration');
            if (!$result) {
                $this->rollback($step['data'], $stepName);
                throw new Exception('Data Migration failed');
            }
            if (!empty($step['volume'])) {
                $result = $this->runStage($step['volume'], $stepName, 'volume check');
            }
            if (!$result) {
                $this->rollback($step['data'], $stepName);
                throw new Exception('Volume Check failed');
            }
        }

        $this->logger->info(PHP_EOL . "Migration completed");
        return true;
    }

    /**
     * @param StageInterface $object
     * @param string $step
     * @param string $stage
     * @return bool
     */
    protected function runStage($object, $step, $stage)
    {
        $this->logger->info(sprintf('%s: %s', PHP_EOL . $step, $stage));

        if ($this->progress->isCompleted($object, $stage)) {
            return true;
        }

        try {
            $result = $object->perform();
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
     * @param RollbackInterface $stage
     * @param string $stepName
     * @return void
     */
    protected function rollback($stage, $stepName)
    {
        if ($stage instanceof RollbackInterface) {
            $this->logger->info(PHP_EOL . 'Error occurred. Rollback.');
            $this->logger->info(sprintf('%s: rollback', PHP_EOL . $stepName));
            try {
                $stage->rollback();
            } catch (\Exception $e) {
                $this->logger->error(PHP_EOL . $e->getMessage());
            }
            $this->progress->reset($stage);
            $this->logger->info(PHP_EOL . 'Please fix errors and run Migration Tool again');
        }
    }
}
