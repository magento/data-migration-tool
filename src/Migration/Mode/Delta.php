<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Step\DeltaInterface;
use Migration\App\Step\Progress;
use Migration\Logger\Logger;
use Migration\Exception;

/**
 * Class Delta
 */
class Delta implements \Migration\App\Mode\ModeInterface
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
     * @var int
     */
    protected $autoRestart;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepList $stepList
     * @param int $autoRestart
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepList $stepList,
        $autoRestart = 5
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepList = $stepList;
        $this->autoRestart = $autoRestart;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsageHelp()
    {
        return <<<USAGE

Delta mode usage information:

 Migrates delta data that appears after main data migration

USAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        do {
            $steps = $this->stepList->getSteps('delta');
            foreach ($steps as $step) {
                if ($step instanceof DeltaInterface) {
                    $this->logger->info(sprintf('%s: %s', PHP_EOL . $step->getTitle(), 'delta'));
                    try {
                        $result = $step->delta();
                    } catch (\Exception $e) {
                        $this->logger->error(PHP_EOL . $e->getMessage());
                        $result = false;
                    }
                    if ($result) {
                        $this->progress->saveResult($step, 'delta', $result);
                    } else {
                        throw new Exception('Delta delivering failed');
                    }
                }
            }
            $this->logger->info(PHP_EOL . 'Migration completed successfully');
            $this->progress->clearLockFile();
            if ($this->autoRestart) {
                $this->logger->info(PHP_EOL . "Automatic restart in {$this->autoRestart} sec. Use CTRL-C to abort");
                sleep($this->autoRestart);
            }
        } while ($this->autoRestart);
        return true;
    }
}
