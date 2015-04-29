<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

/**
 * Processing logger handler creation for migration application
 */
class Manager
{
    /** Log levels */
    const LOG_LEVEL_ERROR = 'error';

    const LOG_LEVEL_INFO = 'info';

    const LOG_LEVEL_DEBUG = 'debug';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ConsoleHandler
     */
    protected $consoleHandler;

    /**
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * @var string|null
     */
    protected $logLevel = null;

    /**
     * @var array
     */
    protected $logLevels = [
        self::LOG_LEVEL_ERROR => Logger::ERROR,
        self::LOG_LEVEL_INFO => Logger::INFO,
        self::LOG_LEVEL_DEBUG => Logger::DEBUG
    ];

    /**
     * @param Logger $logger
     * @param ConsoleHandler $consoleHandler
     * @param FileHandler $fileHandler
     */
    public function __construct(Logger $logger, ConsoleHandler $consoleHandler, FileHandler $fileHandler)
    {
        $this->logger = $logger;
        $this->handlers = [$consoleHandler, $fileHandler];
    }

    /**
     * @param string $logLevel
     * @return $this
     */
    public function process($logLevel = self::LOG_LEVEL_INFO)
    {
        $logLevel = strtolower($logLevel);
        if (empty($this->logLevels[$logLevel])) {
            $this->logger->error("Invalid log level '$logLevel' provided.");
            $logLevel = self::LOG_LEVEL_INFO;
        }
        foreach ($this->handlers as $handler) {
            $handler->setLevel($this->logLevels[$logLevel]);
            $this->logger->pushHandler($handler);
        }
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }
}
