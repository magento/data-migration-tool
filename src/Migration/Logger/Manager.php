<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var \Monolog\Handler\AbstractHandler[]
     */
    protected $handlers;

    /**
     * @var MessageFormatter
     */
    protected $formatter;

    /**
     * @var MessageProcessor
     */
    protected $processor;

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
     * @param MessageFormatter $formatter
     * @param MessageProcessor $messageProcessor
     */
    public function __construct(
        Logger $logger,
        ConsoleHandler $consoleHandler,
        FileHandler $fileHandler,
        MessageFormatter $formatter,
        MessageProcessor $messageProcessor
    ) {
        $this->logger = $logger;
        $this->handlers = [$consoleHandler, $fileHandler];
        $this->formatter = $formatter;
        $this->processor = $messageProcessor;
    }

    /**
     * Process
     *
     * @param string $logLevel
     * @return $this
     * @throws \Migration\Exception
     */
    public function process($logLevel = self::LOG_LEVEL_INFO)
    {
        $logLevel = strtolower($logLevel);
        if (empty($this->logLevels[$logLevel])) {
            throw new \Migration\Exception("Invalid log level '$logLevel' provided.");
        }
        foreach ($this->handlers as $handler) {
            $handler->setLevel($this->logLevels[$logLevel])->setFormatter($this->formatter);
            $this->logger->pushHandler($handler);
        }
        $this->logger->pushProcessor([$this->processor, 'setExtra']);
        $this->logLevel = $logLevel;
        return $this;
    }

    /**
     * Get log level
     *
     * @return null|string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }
}
