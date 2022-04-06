<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\AbstractHandler;
use Psr\Log\LogLevel;

/**
 * Processing logger handler creation for migration application
 */
class ConsoleHandler extends AbstractHandler implements HandlerInterface, FormattableHandlerInterface
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
     * @var FormatterInterface
     */
    private $formatter;

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
     * @inheritdoc
     */
    public function handle(array $record): bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $record['formatted'] = $this->getFormatter()->format($record);
        switch ($record['level']) {
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
                echo PHP_EOL . $this->colorize($record['formatted'], self::COLOR_RED);
                break;
            case LogLevel::WARNING:
                echo PHP_EOL . $this->colorize($record['formatted'], self::COLOR_YELLOW);
                break;
            case LogLevel::NOTICE:
                echo PHP_EOL . $this->colorize($record['formatted'], self::COLOR_BLUE);
                break;
            default:
                echo PHP_EOL . $record['formatted'];
        }
        return false === $this->bubble;
    }

    /**
     * Sets the formatter.
     *
     * @param FormatterInterface $formatter
     */
    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->formatter = $formatter;
        return $this;
    }

    /**
     * Gets the formatter.
     *
     * @return FormatterInterface
     */
    public function getFormatter(): FormatterInterface
    {
        if (!$this->formatter) {
            throw new \LogicException('No formatter has been set and this handler does not have a default formatter');
        }
        return $this->formatter;
    }
}
