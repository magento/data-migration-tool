<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * Class ConsoleOutputFactory
 */
class ConsoleOutputFactory
{
    /**
     * @param int $verbosity
     * @param null $decorated
     * @param OutputFormatterInterface|null $formatter
     * @return ConsoleOutput
     */
    public function create(
        $verbosity = ConsoleOutput::VERBOSITY_NORMAL,
        $decorated = null,
        OutputFormatterInterface $formatter = null
    ) {
        return new ConsoleOutput($verbosity, $decorated, $formatter);
    }
}
