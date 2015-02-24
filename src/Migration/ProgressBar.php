<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration;

use \Symfony\Component\Console\Output\ConsoleOutput;

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
     * @param ConsoleOutput $output
     */
    public function __construct(ConsoleOutput $output)
    {
        parent::__construct($output);
    }
}
