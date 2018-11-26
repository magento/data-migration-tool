<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Mode\StepList;
use Migration\App\Progress;
use Migration\Logger\Logger;
use Migration\Exception;
use Migration\Config;

/**
 * Class Settings
 */
class Settings extends AbstractMode implements \Migration\App\Mode\ModeInterface
{
    /**
     * @inheritdoc
     */
    protected $mode = 'settings';

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepListFactory $stepListFactory
     * @param \Migration\Config $configReader
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepListFactory $stepListFactory,
        Config $configReader
    ) {
        parent::__construct($progress, $logger, $stepListFactory);
        $this->configReader = $configReader;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        /** @var StepList $steps */
        $steps = $this->stepListFactory->create(['mode' => 'settings']);
        $this->runIntegrity($steps);

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
    private function runIntegrity(StepList $steps)
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
     * Run data
     *
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    private function runData(array $step, $stepName)
    {
        if (!$this->runStage($step['data'], $stepName, 'data migration')) {
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
    private function runVolume(array $step, $stepName)
    {
        if (!$this->runStage($step['volume'], $stepName, 'volume check')) {
            $this->logger->warning('Volume Check failed');
        }
    }
}
