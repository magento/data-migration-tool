<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\SetupDeltaLog;
use Migration\App\Mode\StepList;
use Migration\App\Progress;
use Migration\App\Step\RollbackInterface;
use Migration\Logger\Logger;
use Migration\Exception;
use Migration\Config;

/**
 * Class Migration
 */
class Data extends AbstractMode implements \Migration\App\Mode\ModeInterface
{
    /**
     * @inheritdoc
     */
    protected $mode = 'data';

    /**
     * @var SetupDeltaLog
     */
    protected $setupDeltaLog;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepListFactory $stepListFactory
     * @param SetupDeltaLog $setupDeltaLog
     * @param \Migration\Config $configReader
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepListFactory $stepListFactory,
        SetupDeltaLog $setupDeltaLog,
        Config $configReader
    ) {
        parent::__construct($progress, $logger, $stepListFactory);
        $this->setupDeltaLog = $setupDeltaLog;
        $this->configReader = $configReader;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        /** @var StepList $steps */
        $steps = $this->stepListFactory->create(['mode' => 'data']);
        $this->runIntegrity($steps);
        $this->setupDeltalog();

        foreach ($steps->getSteps() as $stepName => $step) {
            if (empty($step['data'])) {
                continue;
            }
            $this->runData($step, $stepName);
            if (!empty($step['volume'])) {
                $this->runVolume($step, $stepName);
            }
        }

        $this->logger->info('Migration completed');
        return true;
    }

    /**
     * Run integrity
     *
     * @param StepList $steps
     * @throws Exception
     * @return void
     */
    protected function runIntegrity(StepList $steps)
    {
        $autoResolve = $this->configReader->getOption(Config::OPTION_AUTO_RESOLVE);
        $result = true;
        foreach ($steps->getSteps() as $stepName => $step) {
            if (!empty($step['integrity'])) {
                $result = $this->runStage($step['integrity'], $stepName, 'integrity check') && $result;
            }
        }
        if (!$result && !$autoResolve) {
            $this->logger->notice($this->autoResolveMessage);
            throw new Exception('Integrity Check failed');
        }
    }

    /**
     * Setup triggers
     *
     * @throws Exception
     * @return void
     */
    protected function setupDeltalog()
    {
        if (!$this->runStage($this->setupDeltaLog, 'Stage', 'setup triggers')) {
            throw new Exception('Setup triggers failed');
        }
    }

    /**
     * Run data
     *
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    protected function runData(array $step, $stepName)
    {
        if (!$this->runStage($step['data'], $stepName, 'data migration')) {
            $this->rollback($step['data'], $stepName);
            throw new Exception('Data Migration failed');
        }
    }

    /**
     * Run volume
     *
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    protected function runVolume(array $step, $stepName)
    {
        if (!$this->runStage($step['volume'], $stepName, 'volume check')) {
            $this->rollback($step['data'], $stepName);
            if ($this->configReader->getStep('delta', $stepName)) {
                $this->logger->warning('Volume Check failed');
            } else {
                throw new Exception('Volume Check failed');
            }
        }
    }

    /**
     * Rollback
     *
     * @param RollbackInterface $stage
     * @param string $stepName
     * @return void
     */
    protected function rollback($stage, $stepName)
    {
        if ($stage instanceof RollbackInterface) {
            $this->logger->info('Error occurred. Rollback.');
            $this->logger->info(sprintf('%s: rollback', $stepName));
            try {
                $stage->rollback();
            } catch (\Migration\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $this->progress->reset($stage);
            $this->logger->info('Please fix errors and run Migration Tool again');
        }
    }
}
