<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

class Logger
{
    /** Levels of logging */
    const LOG_LEVEL_DEBUG = 'DEBUG';

    const LOG_LEVEL_INFO = 'INFO';

    const LOG_LEVEL_NONE = 'NONE';

    /**
     * @var array
     */
    protected $logLevelRates = [
        self::LOG_LEVEL_NONE  => 0,
        self::LOG_LEVEL_DEBUG => 1,
        self::LOG_LEVEL_INFO  => 2
    ];

    /**
     * @var WriterInterface[]
     */
    protected $writers = [];

    /**
     * @param WriterInterface $writer
     */
    public function addWriter(WriterInterface $writer)
    {
        $this->writers[] = $writer;
    }

    /**
     * @return WriterInterface[]
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Log some message
     *
     * @param $message
     * @param string $logLevel
     */
    public function log($message, $logLevel = self::LOG_LEVEL_INFO)
    {
        foreach ($this->writers as $writer) {
            if ($this->logLevelRates[$logLevel] <= $this->logLevelRates[$writer->getLoggingLevel()]) {
                $writer->write($message, $logLevel);
            }
        }
    }

    /**
     * Log message with info level
     *
     * @param string $message
     */
    public function logInfo($message)
    {
        $this->log($message, self::LOG_LEVEL_INFO);
    }

    /**
     * Log message with debug level
     *
     * @param string $message
     */
    public function logDebug($message)
    {
        $this->log($message, self::LOG_LEVEL_DEBUG);
    }

    /**
     * Log success message
     *
     * @param string $message
     */
    public function logSuccess($message)
    {
        foreach ($this->writers as $writer) {
            $writer->writeSuccess($message);
        }
    }

    /**
     * Log error message
     *
     * @param string $message
     */
    public function logError($message)
    {
        foreach ($this->writers as $writer) {
            $writer->writeError($message);
        }
    }

    /**
     * Check if log level is correct
     *
     * @param $logLevel
     * @return bool
     */
    public function isLogLevelValid($logLevel)
    {
        if (isset($this->logLevelRates[$logLevel])) {
            return true;
        }
        return false;
    }
}
