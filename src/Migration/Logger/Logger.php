<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

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
    public function addRecord($level, $message, array $context = [])
    {
        parent::addRecord($level, $message, $context);
        self::$messages[$level][] = $message;
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
