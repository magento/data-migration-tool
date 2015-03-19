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
     * @param Progress $progress
     * @param Logger $logger
     */
    public function __construct(
        Progress $progress,
        Logger $logger
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function run(array $steps)
    {
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
        $this->logger->info(PHP_EOL . "Migration completed");
        $this->progress->clearLockFile();
    }
}
