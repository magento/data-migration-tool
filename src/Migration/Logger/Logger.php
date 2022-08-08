<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

use Monolog\DateTimeImmutable;

/**
 * Processing logger handler creation for migration application
 */
class Logger extends \Monolog\Logger
{
    /**
     * All messages from logger
     *
     * @var array
     */
    protected static $messages = [];

    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct($name = 'Migration', array $handlers = [], array $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * @inheritdoc
     */
    public function addRecord(int $level, string $message, array $context = [], DateTimeImmutable $datetime = null): bool
    {
        $processed = parent::addRecord($level, $message, $context);
        self::$messages[$level][] = $message;
        return $processed;
    }

    /**
     * Returns all log messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return self::$messages;
    }

    /**
     * Clear all log messages
     *
     * @return void
     */
    public static function clearMessages()
    {
        self::$messages = [];
    }
}
