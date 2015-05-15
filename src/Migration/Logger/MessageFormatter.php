<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Logger;

/**
 * Format logger messages corresponding to verbosity level
 */
class MessageFormatter extends \Monolog\Formatter\LineFormatter implements \Monolog\Formatter\FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    protected $format;

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $this->format = $this->getLevelFormat($record['level_name']);
        return parent::format($record);
    }

    /**
     * @param string $levelName
     * @return string
     */
    protected function getLevelFormat($levelName)
    {
        switch ($levelName) {
            case 'INFO':
                $format = "[%datetime%][INFO]%extra.mode%%extra.stage%%extra.step%: %message%";
                break;
            case 'DEBUG':
                $format = "[%datetime%][DEBUG]%extra.mode%%extra.stage%%extra.step%%extra.table%: %message%";
                break;
            case 'ERROR':
                $format = "[%datetime%][ERROR]: %message%";
                break;
            default:
                $format = "%message%";
        }
        return $format;
    }
}
