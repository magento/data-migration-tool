<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ProgressTest
 */
class ProgressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Console\Output\ConsoleOutput|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $output;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Step\Progress
     */
    protected $progress;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryWriteFactory;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()->getMock();
        $this->output = $this->getMockBuilder('\Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output->expects($this->any())->method('getVerbosity')
            ->will($this->returnValue(OutputInterface::VERBOSITY_QUIET));
        $this->directoryWriteFactory = $this->getMockBuilder('Magento\Framework\Filesystem\Directory\WriteFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->progress = new Progress($this->config, $this->output, $this->directoryWriteFactory);

        $step = $this->getMock('\Migration\Step\StepInterface');
        $this->progress->setStep($step);
    }

    public function testStart()
    {
        $this->progress->start(50);
        $this->assertEquals($this->progress->getMaxSteps(), 50);
    }

    public function testAdvance()
    {
        $this->progress->advance();
        $this->assertEquals($this->progress->getProgress(), 1);
    }

    public function testFinish()
    {
        $this->progress->finish();
        $this->assertEquals(Progress::COMPLETED, $this->progress->getStatus());
    }

    public function testGetStepProgress()
    {
        $this->progress->start();
        $this->assertEquals(0, $this->progress->getStepProgress());

        $this->progress->advance();
        $this->assertEquals(1, $this->progress->getStepProgress());

        $this->progress->advance(2);
        $this->assertEquals(3, $this->progress->getStepProgress());
    }
}
