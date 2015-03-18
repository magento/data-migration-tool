<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;
use Migration\Config;
use Migration\Exception;

/**
 * Class Manager
 */
class Manager
{
    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Progress
     */
    protected $progress;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param Factory $factory
     * @param Config $config
     */
    public function __construct(Progress $progress, Logger $logger, Factory $factory, Config $config)
    {
        $this->progress = $progress;
        $this->factory = $factory;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Run steps
     *
     * @return $this
     * @throws Exception
     */
    public function runSteps()
    {
        $steps = $this->config->getSteps();
        $stepInstances = [];
        foreach ($steps as $stepClass) {
            /** @var StepInterface $step */
            $stepInstances[] = $this->factory->create($stepClass);
        }

        $result = true;
        foreach ($stepInstances as $step) {
            $result = $result && $this->runStep($step, 'integrity check');
            if (!$result) {
                throw new Exception('Integrity Check failed');
            }
        }

        foreach ($stepInstances as $step) {
            if (!$this->runStep($step, 'data migration')) {
                $this->logger->info(PHP_EOL . 'Error occured. Rollback.');
                $this->runStep($step, 'rollback');
                throw new Exception('Data Migration failed');
            }

            if (!$this->runStep($step, 'volume check')) {
                $this->logger->info(PHP_EOL . 'Error occured. Rollback.');
                $this->runStep($step, 'rollback');
                throw new Exception('Volume Check failed');
            }
        }

        $this->logger->info(PHP_EOL . "Migration completed");
        $this->progress->clearLockFile();
        return $this;
    }

    /**
     * @param StepInterface $step
     * @param string $stepPart
     * @return bool
     */
    public function runStep(StepInterface $step, $stepPart)
    {
        $this->logger->info(sprintf('%s: %s', PHP_EOL . $step->getTitle(), $stepPart));

        if ($this->progress->isCompleted($step, $stepPart)) {
            return true;
        }

        $result = false;
        try {
            switch ($stepPart) {
                case 'integrity check':
                    $result = $step->integrity();
                    break;
                case 'data migration':
                    $result = $step->run();
                    break;
                case 'volume check':
                    $result = $step->volumeCheck();
                    break;
                case 'rollback':
                    $step->rollback();
                    $this->progress->resetStep($step);
                    $this->logger->info(PHP_EOL . 'Please fix errors and run Migration Tool again');
                    break;
            }
        } catch (\Exception $e) {
            $this->logger->error(PHP_EOL . $e->getMessage());
            return false;
        }

        if ($result) {
            $this->progress->saveResult($step, $stepPart, $result);
        }

        return $result;
    }
}
