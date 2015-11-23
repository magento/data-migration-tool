<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Mode\StepList;
use Migration\Exception;

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
     * {@inheritdoc}
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
     * @param StepList $steps
     * @throws Exception
     * @return void
     */
    private function runIntegrity(StepList $steps)
    {
        $result = true;
        foreach ($steps->getSteps() as $stepName => $step) {
            if (!empty($step['integrity'])) {
                $result = $this->runStage($step['integrity'], $stepName, 'integrity check') && $result;
            }
        }
        if (!$result) {
            throw new Exception('Integrity Check failed');
        }
    }

    /**
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
