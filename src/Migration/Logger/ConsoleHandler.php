<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

/**
 * Processing logger handler creation for migration application
 */
class ConsoleHandler extends \Monolog\Handler\AbstractHandler implements \Monolog\Handler\HandlerInterface
{
    const COLOR_RESET   = '0';
    const COLOR_BLACK   = '0;30';
    const COLOR_RED     = '0;31';
    const COLOR_GREEN   = '0;32';
    const COLOR_YELLOW  = '0;33';
    const COLOR_BLUE    = '0;34';
    const COLOR_MAGENTA = '0;35';
    const COLOR_CYAN    = '0;36';
    const COLOR_WHITE   = '0;37';

    /**
     * Paint the message to specified color
     *
     * @param string $string
     * @param string $color
     * @return string
     */
    protected function colorize($string, $color)
    {
        return "\x1b[{$color}m" . $string . "\x1b[" . self::COLOR_RESET . "m";
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $isError = in_array($record['level'], [Logger::ERROR, Logger::CRITICAL]);
        if ($isError) {
            echo $this->colorize($record['message'], self::COLOR_RED);
        } else {
            echo $record['message'];
        }
        echo PHP_EOL;
        return false === $this->bubble;
    }
}
