<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger\Writer;

use Migration\Logger;
use Zend\Console\ColorInterface;

class Console implements Logger\WriterInterface
{
    /**
     * Console
     *
     * @var \Zend\Console\Adapter\AdapterInterface
     */
    protected $console;

    /**
     * logging level for current writer
     *
     * @var string
     */
    protected $loggingLevel = Logger\Logger::LOG_LEVEL_INFO;

    /**
     * @param Console\Creator $consoleCreator
     */
    public function __construct(
        Console\Creator $consoleCreator
    ) {
        $this->console = $consoleCreator->create();
    }

    /**
     * {@inheritdoc}
     */
    public function write($message, $logLevel = Logger\Logger::LOG_LEVEL_INFO)
    {
        $color = ColorInterface::LIGHT_BLUE;
        if ($logLevel == Logger\Logger::LOG_LEVEL_DEBUG) {
            $color = ColorInterface::GRAY;
        }
        $this->console->writeLine($message, $color);
    }

    /**
     * {@inheritdoc}
     */
    public function writeSuccess($message)
    {
        $this->console->writeLine("[SUCCESS]: " . $message, ColorInterface::LIGHT_GREEN);
    }

    /**
     * {@inheritdoc}
     */
    public function writeError($message)
    {
        $this->console->writeLine("[ERROR]: " . $message, ColorInterface::LIGHT_RED);
    }

    /**
     * {@inheritdoc}
     */
    public function setLoggingLevel($level)
    {
        $this->loggingLevel = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoggingLevel()
    {
        return $this->loggingLevel;
    }
}
