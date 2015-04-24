<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Migration\Logger\Manager as LogManager;

/**
 * Class ProgressBar
 */
class ProgressBar extends \Symfony\Component\Console\Helper\ProgressBar
{
    /**
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * @param ConsoleOutput $consoleOutput
     * @param NullOutput $nullOutput
     */
    public function __construct(ConsoleOutput $consoleOutput, NullOutput $nullOutput)
    {
        if (LogManager::getLogLevel() == LogManager::LOG_LEVEL_ERROR) {
            parent::__construct($nullOutput);
        } else {
            parent::__construct($consoleOutput);
        }
        $this->setFormat('%percent%% [%bar%]');
    }
}
