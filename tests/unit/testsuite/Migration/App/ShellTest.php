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

    protected function setUp()
    {
        $this->filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $read = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($read);
        $this->logger = $this->getMock('\Migration\Logger\Logger', [], [], '', false);
        $this->consoleLogWriter = $this->getMock('\Migration\Logger\Writer\Console', [], [], '', false);;
        $config = $this->getMock('\Migration\Config', [], [], '', false);
        $this->shell = new Shell($this->filesystem, $config, $this->logger, $this->consoleLogWriter, '');
    }

    public function testRun()
    {
        $args = ['--config', 'file/to/config.xml', '--type', 'mapStep'];
        $this->shell->setRawArgs($args);
        $this->logger->expects($this->at(1))->method('logInfo')->with('Loaded custom config file: file/to/config.xml');
        $this->logger->expects($this->at(2))->method('logInfo')->with('mapStep');
        $result = $this->shell->run();
        $this->assertSame($this->shell, $result);
    }

    public function testRunVerboseValid()
    {
        $level = 'DEBUG';
        $this->shell->setRawArgs(['--verbose', $level]);
        $this->logger->expects($this->once())->method('isLogLevelValid')->with($level)->will($this->returnValue(true));
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
