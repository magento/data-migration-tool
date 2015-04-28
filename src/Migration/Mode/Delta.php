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

/**
 * Class Delta
 */
class Delta implements \Migration\App\Mode\ModeInterface
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
    protected $shouldComplete = false;


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
            $steps = $this->stepListFactory->create(['mode' => 'delta']);
            /**
             * @var string $stepName
             * @var StageInterface[] $step
             */
            foreach ($steps->getSteps() as $stepName => $step) {
                if (empty($step['delta'])) {
                    continue;
                }
                $this->logger->info(sprintf('%s: %s', PHP_EOL . $stepName, 'delta'));
                try {
                    $result = $step['delta']->perform();
                } catch (\Exception $e) {
                    $this->logger->error(PHP_EOL . $e->getMessage());
                    $result = false;
                }
                if ($result) {
                    $this->progress->saveResult($step['delta'], 'delta', $result);
                } else {
                    throw new Exception('Delta delivering failed');
                }

                if (!empty($step['volume'])) {
                    $this->logger->info(sprintf('%s: %s', PHP_EOL . $stepName, 'volume'));
                    $result = $step['volume']->perform();
                    if (!$result) {
                        throw new Exception('Volume check failed');
                    }
                }
            }
            $this->logger->info(PHP_EOL . 'Migration completed successfully');
            if ($this->autoRestart) {
                $this->logger->info(PHP_EOL . "Automatic restart in {$this->autoRestart} sec. Use CTRL-C to abort");
                sleep($this->autoRestart);
            }
        } while ($this->autoRestart);
        return true;
    }
}
