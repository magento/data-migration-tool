<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

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
     * @var \Migration\Logger\Writer\Console|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $consoleLogWriter;

    /**
     * @var \Migration\Steps\StepFactory
     */
    protected $stepFactory;

    protected function setUp()
    {
        $this->filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $read = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($read);
        $this->logger = $this->getMock('\Migration\Logger\Logger', [], [], '', false);
        $this->consoleLogWriter = $this->getMock('\Migration\Logger\Writer\Console', [], [], '', false);;
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()->getMock();
        $this->stepFactory = $this->getMockBuilder('\Migration\Steps\StepFactory')->setMethods(['getSteps'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shell = new Shell(
            $this->filesystem,
            $config,
            $this->logger,
            $this->stepFactory,
            $this->consoleLogWriter,
            ''
        );
    }

    /**
     * @dataProvider runDataProvider
     * @param array $args
     * @param string $outputContains
     */
    public function testRun($args, $outputContains)
    {
        $this->shell->setRawArgs($args);
        $this->logger->expects($this->once())->method('logInfo')->with($outputContains);
        $step = $this->getMock('\Migration\Steps\StepInterface');
        $this->stepFactory->expects($this->once())->method('getSteps')->will($this->returnValue([$step]));
        $result = $this->shell->run();
        $this->assertSame($this->shell, $result);
    }

    public function runDataProvider()
    {
        return array(
            array(['--config', 'file/to/config.xml'], 'file/to/config.xml'),
            array(['--type', 'mapStep'], 'mapStep')
        );
    }

    public function testRunVerboseValid()
    {
        $level = 'DEBUG';
        $this->shell->setRawArgs(['--verbose', $level]);
        $this->logger->expects($this->once())->method('isLogLevelValid')->with($level)->will($this->returnValue(true));
        $this->stepFactory->expects($this->once())->method('getSteps')->will($this->returnValue([]));
        $this->consoleLogWriter->expects($this->once())->method('setLoggingLevel')->with($level);
        $this->shell->run();
    }

    public function testRunVerboseInvalid()
    {
        $level = 'DUMMY';
        $this->shell->setRawArgs(['--verbose', $level]);
        $this->logger->expects($this->once())->method('isLogLevelValid')->with($level)->will($this->returnValue(false));
        $this->consoleLogWriter->expects($this->never())->method('setLoggingLevel');
        $this->logger->expects($this->once())->method('logError');
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
