<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Mode\StepList;
use Migration\App\Step\Progress;
use Migration\Logger\Logger;
use Migration\Exception;

/**
 * Class Settings
 */
class Settings implements \Migration\App\Mode\ModeInterface
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
        StepList $stepList
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepList = $stepList;
    }

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
        $steps = $this->stepList->getSteps('settings');
        foreach ($steps as $step) {
            $result = $result && $this->runStage($step, 'integrity', 'integrity check');
            if (!$result) {
                throw new Exception('Integrity Check failed');
            }
        }

        foreach ($steps as $step) {
            $result = $this->runStage($step, 'run', 'data migration');
            if (!$result) {
                throw new Exception('Data Migration failed');
            }
            $result = $this->runStage($step, 'volumeCheck', 'volume check');
            if (!$result) {
                throw new Exception('Volume Check failed');
            }
        }

        $this->logger->info(PHP_EOL . "Migration completed");
        $this->progress->clearLockFile();
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
}
