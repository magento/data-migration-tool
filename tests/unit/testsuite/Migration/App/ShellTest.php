<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Class ShellTest
 */
class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Logger\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logManager;

    /**
     * @var \Migration\App\Mode\ModeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modeFactory;

    /**
     * @var Progress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    protected function setUp()
    {
        $_SERVER['argv'] = [];
        $this->filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $read = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($read);
        $this->logger = $this->getMock('\Migration\Logger\Logger', [], [], '', false);
        $this->logManager = $this->getMock('\Migration\Logger\Manager', [], [], '', false);
        /** @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()->getMock();
        $this->modeFactory = $this->getMockBuilder('\Migration\App\Mode\ModeFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->progress = $this->getMockBuilder('\Migration\App\Progress')
            ->setMethods(['reset'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shell = new Shell(
            $this->filesystem,
            $config,
            $this->modeFactory,
            $this->logger,
            $this->logManager,
            $this->progress,
            ''
        );
    }

    public function testRun()
    {
        $args = ['data', '--config', 'file/to/config.xml'];
        $this->shell->setRawArgs($args);
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('run');
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);
        $result = $this->shell->run();
        $this->assertSame($this->shell, $result);
    }

    public function testRunFromBin()
    {
        $args = ['migrate', 'data', '--config', 'file/to/config.xml'];
        $this->shell->setRawArgs($args);
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('run');
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);
        $result = $this->shell->run();
        $this->assertSame($this->shell, $result);
    }

    public function testRunVerboseValid()
    {
        $level = 'DEBUG';
        $this->shell->setRawArgs(['data', '--verbose', $level, '--config', 'file/to/config.xml']);
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('run');
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);
        $this->logManager->expects($this->once())->method('process')->with($level);
        $this->shell->run();
    }

    public function testRunClearProgress()
    {
        $this->shell->setRawArgs(['data', '--reset', '--config', 'file/to/config.xml']);
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('run');
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);
        $this->progress->expects($this->once())->method('reset');
        $this->shell->run();
    }

    public function testRunWithException1()
    {
        $this->shell->setRawArgs(['data', '--config', 'file/to/config.xml']);
        $this->logManager->expects($this->once())->method('process');
        $errorMessage = 'test error message';
        $exception = new \Exception($errorMessage);
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('run')->willThrowException($exception);
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);

        $this->logger->expects($this->at(0))->method('error')->with(
            'Application failed with exception: test error message',
            []
        );
        $this->logger->expects($this->at(1))->method('error')->with($this->stringContains('{main}'));
        $this->shell->run();
    }

    public function testRunWithException2()
    {
        $this->shell->setRawArgs(['data', '--config', 'file/to/config.xml']);
        $this->logManager->expects($this->once())->method('process');
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('run')->willThrowException(
            new \Migration\Exception('test error message')
        );
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);
        $this->logger->expects($this->once())->method('error')->with($this->stringContains('test error message'));
        $this->shell->run();
    }

    public function testRunShowHelp()
    {
        ob_start();
        $result = $this->shell->run();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertSame($this->shell, $result);
        $this->assertContains('Usage:', $output);

        $this->shell->setRawArgs(['help']);
        ob_start();
        $result = $this->shell->run();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertSame($this->shell, $result);
        $this->assertContains('Usage:', $output);
    }

    public function testRunShowModeHelp()
    {
        $mode = $this->getMock('\Migration\Mode\Data', [], [], '', false);
        $mode->expects($this->any())->method('getUsageHelp')->willReturn('mode help');
        $this->modeFactory->expects($this->once())->method('create')->with('data')->willReturn($mode);
        $this->shell->setRawArgs(['data', 'help']);
        ob_start();
        $result = $this->shell->run();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertSame($this->shell, $result);
        $this->assertContains('Usage:', $output);
        $this->assertContains('mode help', $output);
    }

    public function testGetUsageHelp()
    {
        $result = $this->shell->getUsageHelp();
        $this->assertContains('Usage:', $result);
    }
}
