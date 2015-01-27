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
     * @var \Magento\Framework\Filesystem\Driver\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()->getMock();
        $this->output = $this->getMockBuilder('\Symfony\Component\Console\Output\ConsoleOutput')
            ->disableOriginalConstructor()
            ->getMock();
        $this->output->expects($this->any())->method('getVerbosity')
            ->will($this->returnValue(OutputInterface::VERBOSITY_QUIET));
        $this->filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem\Driver\File')
            ->setMethods(['isExists', 'filePutContents', 'fileGetContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->expects($this->any())->method('filePutContents')->will($this->returnValue(true));
        $this->filesystem->expects($this->any())->method('isExists')->will($this->returnValue(false));
        $this->progress = new Progress($this->config, $this->output, $this->filesystem);

        $step = $this->getMock('\Migration\Step\StepInterface');
        $this->progress->setStep($step);
    }

    public function testLoadProgress()
    {
        $filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem\Driver\File')
            ->setMethods(['isExists', 'fileGetContents'])
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->any())->method('isExists')->will($this->returnValue(true));
        $step = $this->getMock('\Migration\Step\StepInterface');
        $progress = sprintf(
            'a:1:{s:%s:"%s";a:2:{s:6:"status";s:11:"in_progress";s:8:"progress";i:7;}}',
            strlen(get_class($step)),
            get_class($step)
        );
        $filesystem->expects($this->any())->method('fileGetContents')->will($this->returnValue($progress));

        $this->progress = new Progress($this->config, $this->output, $filesystem);
        $this->progress->setStep($step);
        $this->assertEquals(7, $this->progress->getProgress());
        $this->assertEquals(7, $this->progress->getStepProgress());
    }

    public function testStart()
    {
        $this->progress->start(50);
        $this->assertEquals($this->progress->getMaxSteps(), 50);
    }

    public function testAdvance()
    {
        $this->progress->advance(5);
        $this->assertEquals(5, $this->progress->getStepProgress());
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

    public function testCheckStep()
    {
        $filesystem = $this->getMockBuilder('\Magento\Framework\Filesystem\Driver\File')
            ->setMethods(['isExists'])
            ->disableOriginalConstructor()
            ->getMock();
        $filesystem->expects($this->any())->method('isExists')->will($this->returnValue(false));
        $this->setExpectedException('Exception', 'Step is not specified');
        $progress = new Progress($this->config, $this->output, $filesystem);
        $progress->start();

    }
}
