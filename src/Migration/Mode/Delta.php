<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Mode;

use Migration\App\Progress;
use Migration\App\Step\StageInterface;
use Migration\Logger\Logger;
use Migration\Exception;
use Migration\App\Mode\StepList;

/**
 * Class Delta
 */
class Delta extends AbstractMode implements \Migration\App\Mode\ModeInterface
{
    /**
     * @var \Migration\App\Mode\StepList
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
     * @var int
     */
    protected $autoRestart;

    /**
     * @inheritdoc
     */
    protected $mode = 'delta';

    /**
     * @var bool
     */
    protected $canBeCompleted = false;


    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param \Migration\App\Mode\StepListFactory $stepListFactory
     * @param int $autoRestart
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        \Migration\App\Mode\StepListFactory $stepListFactory,
        $autoRestart = 5
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->stepListFactory = $stepListFactory;
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
            /** @var StepList $steps */
            $steps = $this->stepListFactory->create(['mode' => 'delta']);
            /**
             * @var string $stepName
             * @var StageInterface[] $step
             */
            foreach ($steps->getSteps() as $stepName => $step) {
                if (empty($step['delta'])) {
                    continue;
                }
                $this->runDelta($step, $stepName);
                if (!empty($step['volume'])) {
                    $this->runVolume($step, $stepName);
                }
            }
            $this->logger->info('Migration completed successfully');
            if ($this->autoRestart) {
                $this->logger->info("Automatic restart in {$this->autoRestart} sec. Use CTRL-C to abort");
                sleep($this->autoRestart);
            }
        } while ($this->autoRestart);
        return true;
    }

    /**
     * @param array $step
     * @param string $stepName
     * @throws Exception
     * @return void
     */
    private function runDelta(array $step, $stepName)
    {
        if (!$this->runStage($step['delta'], $stepName, 'delta delivering')) {
            throw new Exception('Delta delivering failed');
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
            throw new Exception('Volume Check failed');
        }
    }
}
