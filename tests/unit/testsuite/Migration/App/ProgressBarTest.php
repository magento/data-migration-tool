<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ProgressBarTest
 */
class ProgressBarTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $output;

    /**
     * @var \Migration\App\ProgressBar
     */
    protected $progressBar;

    public function setUp()
    {
        $this->output = $this->getMock('\Symfony\Component\Console\Output\ConsoleOutput', [], [], '', false);
    }

    public function testCreate()
    {
        $this->progressBar = new ProgressBar($this->output);
        $this->assertInstanceOf('\Migration\App\ProgressBar', $this->progressBar);
    }
}
