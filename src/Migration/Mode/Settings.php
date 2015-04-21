<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\Exception;

/**
 * Class Settings
 */
class Settings extends AbstractMode implements \Migration\App\Mode\ModeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUsageHelp()
    {
        return <<<USAGE

Settings mode usage information:

Migrates store settings
USAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $result = true;
        $steps = $this->stepListFactory->create(['mode' => 'settings']);
        foreach ($steps->getSteps() as $stepName => $step) {
            if (!empty($step['integrity'])) {
                $result = $this->runStage($step['integrity'], $stepName, 'integrity check') && $result;
            }
        }
        if (!$result) {
            throw new Exception('Integrity Check failed');
        }

        foreach ($steps->getSteps() as $stepName => $step) {
            if (empty($step['data'])) {
                continue;
            }
            $result = $this->runStage($step['data'], $stepName, 'data migration');
            if (!$result) {
                throw new Exception('Data Migration failed');
            }
            if (!empty($step['volume'])) {
                $result = $this->runStage($step['volume'], $stepName, 'volume check');
            }
            if (!$result) {
                throw new Exception('Volume Check failed');
            }
        }

        $this->logger->info(PHP_EOL . "Migration completed");
        return true;
    }
}
