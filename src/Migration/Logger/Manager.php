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
    const LOG_LEVEL_NONE = 'none';

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
     * @var array
     */
    protected $logLevels = [
        self::LOG_LEVEL_NONE => Logger::ERROR,
        self::LOG_LEVEL_INFO => Logger::INFO,
        self::LOG_LEVEL_DEBUG => Logger::DEBUG
    ];

    /**
     * @param Logger $logger
     * @param ConsoleHandler $consoleHandler
     */
    public function __construct(Logger $logger, ConsoleHandler $consoleHandler)
    {
        $this->logger = $logger;
        $this->consoleHandler = $consoleHandler;
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
        $this->consoleHandler->setLevel($this->logLevels[$logLevel]);
        $this->logger->pushHandler($this->consoleHandler);
        return $this;
    }
}
