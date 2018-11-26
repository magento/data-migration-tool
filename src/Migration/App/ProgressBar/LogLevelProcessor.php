<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\ProgressBar;

use Migration\App\ProgressBarFactory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Migration\App\ConsoleOutputFactory;
use Migration\Logger\Manager as LogManager;
use Migration\Config;

/**
 * Class LogLevelProcessor
 */
class LogLevelProcessor
{
    const PROGRESS_BAR_FORMAT_OPTION = 'progress_bar_format';

    /**
     * @var LogManager
     */
    protected $logManager;

    /**
     * @var NullOutput
     */
    protected $nullOutput;

    /**
     * @var OutputInterface
     */
    protected $consoleOutput;

    /**
     * @var ConsoleOutputFactory
     */
    protected $consoleOutputFactory;

    /**
     * @var ProgressBarFactory
     */
    protected $progressBarFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * @param LogManager $logManager
     * @param ProgressBarFactory $progressBarFactory
     * @param NullOutput $nullOutput
     * @param ConsoleOutputFactory $consoleOutputFactory
     * @param Config $config
     */
    public function __construct(
        LogManager $logManager,
        ProgressBarFactory $progressBarFactory,
        NullOutput $nullOutput,
        ConsoleOutputFactory $consoleOutputFactory,
        Config $config
    ) {
        $this->logManager = $logManager;
        $this->nullOutput = $nullOutput;
        $this->consoleOutputFactory = $consoleOutputFactory;
        $this->progressBarFactory = $progressBarFactory;
        $this->config = $config;
    }

    /**
     * Get progress bar
     *
     * @return ProgressBar
     */
    protected function getProgressBar()
    {
        if (null == $this->progressBar) {
            $this->progressBar = $this->progressBarFactory->create($this->getOutputInstance());
            $this->progressBar->setFormat($this->config->getOption(self::PROGRESS_BAR_FORMAT_OPTION));
        }
        return $this->progressBar;
    }

    /**
     * Get output instance
     *
     * @return OutputInterface
     */
    protected function getOutputInstance()
    {
        if (null == $this->consoleOutput) {
            $this->consoleOutput = LogManager::LOG_LEVEL_ERROR == $this->logManager->getLogLevel() ?
                $this->nullOutput :
                $this->consoleOutputFactory->create();
        }
        return $this->consoleOutput;
    }

    /**
     * Start
     *
     * @param int $max
     * @param bool $forceLogLevel
     * @return void
     */
    public function start($max, $forceLogLevel = false)
    {
        if ($this->canRun($forceLogLevel)) {
            echo PHP_EOL;
            $max = ($max == 0) ? 1: $max;
            $this->getProgressBar()->start($max);
            $this->getProgressBar()->setOverwrite(true);
        }
    }

    /**
     * Advance
     *
     * @param bool $forceLogLevel
     * @return void
     */
    public function advance($forceLogLevel = false)
    {
        if ($this->canRun($forceLogLevel)) {
            $this->getProgressBar()->advance();
        }
    }

    /**
     * Finish
     *
     * @param bool $forceLogLevel
     * @return void
     */
    public function finish($forceLogLevel = false)
    {
        if ($this->canRun($forceLogLevel)) {
            $this->getProgressBar()->finish();
        }
    }

    /**
     * Can run
     *
     * @param mixed $forceLogLevel
     * @return bool
     */
    protected function canRun($forceLogLevel)
    {
        $canRun = false;
        if ($forceLogLevel == LogManager::LOG_LEVEL_DEBUG
            && $this->logManager->getLogLevel() == LogManager::LOG_LEVEL_DEBUG
        ) {
            $canRun = true;
        } else if ($forceLogLevel == LogManager::LOG_LEVEL_INFO
            && $this->logManager->getLogLevel() == LogManager::LOG_LEVEL_INFO) {
            $canRun = true;
        } else if ($forceLogLevel === false) {
            $canRun = true;
        }
        return $canRun;
    }
}
