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
     * @var \Migration\Step\StepFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stepManager;

    /**
     * @var \Migration\Step\ProgressStep|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progressStep;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $read = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($read);
        $this->logger = $this->getMock('\Migration\Logger\Logger', [], [], '', false);
        $this->logManager = $this->getMock('\Migration\Logger\Manager', [], [], '', false);
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()->getMock();
        $this->stepManager = $this->getMockBuilder('\Migration\Step\StepManager')->setMethods(['runSteps'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->progressStep = $this->getMockBuilder('\Migration\Step\ProgressStep')->setMethods(['clearLockFile'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shell = new Shell(
            $this->filesystem,
            $config,
            $this->stepManager,
            $this->logger,
            $this->logManager,
            $this->progressStep,
            ''
        );
    }

    public function testRun()
    {
        $args = ['--config', 'file/to/config.xml', '--type', 'mapStep'];
        $this->shell->setRawArgs($args);
        $this->logger->expects($this->at(1))->method('info')->with('mapStep');
        $this->stepManager->expects($this->once())->method('runSteps');
        $result = $this->shell->run();
        $this->assertSame($this->shell, $result);
    }

    public function testRunVerboseValid()
    {
        $level = 'DEBUG';
        $this->shell->setRawArgs(['--verbose', $level]);
        $this->stepManager->expects($this->once())->method('runSteps');
        $this->logManager->expects($this->once())->method('process')->with($level);
        $this->shell->run();
    }

    public function testRunClearProgress()
    {
        $this->shell->setRawArgs(['--force']);
        $this->stepManager->expects($this->once())->method('runSteps');
        $this->progressStep->expects($this->once())->method('clearLockFile');
        $this->shell->run();
    }

    public function testRunWithException()
    {
        $this->logManager->expects($this->once())->method('process');
        $errorMessage = 'test error message';
        $exception = new \Exception($errorMessage);
        $this->stepManager->expects($this->once())->method('runSteps')->will($this->throwException($exception));
        $this->logger->expects($this->once())->method('error')->with(
            'Application failed with exception: test error message'
        );
        $this->shell->run();
    }

    public function testRunShowHelp()
    {
        $this->shell->setRawArgs(['help']);
        ob_start();
        $result = $this->shell->run();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertSame($this->shell, $result);
        $this->assertContains('Usage:  php -f', $output);
    }

    public function testGetUsageHelp()
    {
        $result = $this->shell->getUsageHelp();
        $this->assertContains('Usage:  php -f', $result);
    }
}
