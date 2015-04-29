<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\ProgressBar;

use Migration\App\ProgressBarFactory;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Migration\Logger\Manager as LogManager;
use Migration\App\ProgressBar;
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
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    protected $progressBar;

    /**
     * @var NullOutput
     */
    protected $nullOutput;

    /**
     * @var ConsoleOutput
     */
    protected $consoleOutput;

    /**
     * @var ProgressBarFactory
     */
    protected $progressBarFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param LogManager $logManager
     * @param ProgressBarFactory $progressBarFactory
     * @param NullOutput $nullOutput
     * @param ConsoleOutput $consoleOutput
     * @param Config $config
     */
    public function __construct(
        LogManager $logManager,
        ProgressBarFactory $progressBarFactory,
        NullOutput $nullOutput,
        ConsoleOutput $consoleOutput,
        Config $config
    ) {
        $this->logManager = $logManager;
        $this->nullOutput = $nullOutput;
        $this->consoleOutput = $consoleOutput;
        $this->progressBarFactory = $progressBarFactory;
        $this->config = $config;
        $this->initProgressBar();

    }

    protected function initProgressBar()
    {
        $this->progressBar = $this->progressBarFactory->create($this->getOutputInstance());
        $this->progressBar->setFormat($this->config->getOption(self::PROGRESS_BAR_FORMAT_OPTION));
    }

    /**
     * @return ConsoleOutput|NullOutput
     */
    protected function getOutputInstance()
    {
        if ($this->logManager->getLogLevel() == LogManager::LOG_LEVEL_ERROR) {
            return $this->nullOutput;
        }
        return $this->consoleOutput;
    }

    /**
     * @param $max
     * @param bool $forceLogLevel
     */
    public function start($max, $forceLogLevel = false)
    {
        $this->runWithLogLevelConditions('start', [$max], $forceLogLevel);
    }

    /**
     * @param bool $forceLogLevel
     */
    public function advance($forceLogLevel = false)
    {
        $this->runWithLogLevelConditions('advance', [], $forceLogLevel);
    }

    /**
     * @param bool $forceLogLevel
     */
    public function finish($forceLogLevel = false)
    {
        $this->runWithLogLevelConditions('finish', [], $forceLogLevel);
    }

    /**
     * @param string $method
     * @param array $params
     * @param bool|string $forceLogLevel
     */
    protected function runWithLogLevelConditions($method, array $params, $forceLogLevel)
    {
        if ($forceLogLevel == LogManager::LOG_LEVEL_DEBUG
            && $this->logManager->getLogLevel() == LogManager::LOG_LEVEL_DEBUG
        ) {
            call_user_func_array([$this->progressBar, $method], $params);
        } else if ($forceLogLevel == LogManager::LOG_LEVEL_INFO) {
            call_user_func_array([$this->progressBar, $method], $params);
        } else if ($forceLogLevel != LogManager::LOG_LEVEL_DEBUG
            && $this->logManager->getLogLevel() != LogManager::LOG_LEVEL_DEBUG
        ) {
            call_user_func_array([$this->progressBar, $method], $params);
        }
    }
}
