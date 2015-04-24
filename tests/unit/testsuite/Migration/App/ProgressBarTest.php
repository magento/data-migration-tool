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
    protected $consoleOutput;

    /**
     * @var \Symfony\Component\Console\Output\NullOutput
     */
    protected $nullOutput;

    /**
     * @var \Migration\App\ProgressBar
     */
    protected $progressBar;

    public function setUp()
    {
        $this->consoleOutput = $this->getMock('\Symfony\Component\Console\Output\ConsoleOutput', [], [], '', false);
        $this->nullOutput = $this->getMock('\Symfony\Component\Console\Output\NullOutput', [], [], '', false);
    }

    public function testCreate()
    {
        $this->progressBar = new ProgressBar($this->consoleOutput, $this->nullOutput);
        $this->assertInstanceOf('\Migration\App\ProgressBar', $this->progressBar);
    }
}
