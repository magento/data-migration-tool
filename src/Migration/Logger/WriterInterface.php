<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

/**
 * Interface for writing logs to some destination
 */
interface WriterInterface
{
    /**
     * Write message to log destination
     *
     * @param string $message Message to write
     * @param string $logLevel
     * @return bool
     */
    public function write($message, $logLevel = Logger::LOG_LEVEL_NORMAL);

    /**
     * Write message about the success of the operation
     *
     * @param string $message
     * @return mixed
     */
    public function writeSuccess($message);

    /**
     * Write message about the error during the operation
     *
     * @param string $message
     * @return mixed
     */
    public function writeError($message);

    /**
     * Logging level setter
     *
     * @param string $level
     * @return $this
     */
    public function setLoggingLevel($level);

    /**
     * Logging level getter
     *
     * @return string
     */
    public function getLoggingLevel();
}
