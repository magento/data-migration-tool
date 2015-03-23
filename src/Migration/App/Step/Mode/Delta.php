<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step\Mode;

use Migration\App\Step\DeltaInterface;
use Migration\App\Step\Progress;
use Migration\App\Step\ModeInterface;
use Migration\Logger\Logger;
use Migration\Exception;

/**
 * Class Delta
 */
class Delta implements ModeInterface
{
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
    protected $autoRestartSec;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param int $autoRestartSec
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        $autoRestartSec = 5
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->autoRestartSec = $autoRestartSec;
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $steps)
    {
        do {
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
            if ($this->autoRestartSec) {
                $this->logger->info(PHP_EOL . "Automatic restart in {$this->autoRestartSec} sec. Use CTRL-C to abort");
                sleep($this->autoRestartSec);
            }
        } while ($this->autoRestartSec);
    }
}
