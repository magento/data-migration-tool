<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Magento\App;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Shell
     */
    protected $shell;

    /**
     * @var \Magento\Framework\App\Console\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    protected function setUp()
    {
        $filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $read = $this->getMock('\Magento\Framework\Filesystem\Directory\ReadInterface', [], [], '', false);
        $filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturn($read);
        $this->shell = new Shell($filesystem, '');
    }

    public function testRun()
    {
        $result = $this->shell->run();
        $this->assertSame($this->shell, $result);
    }

    public function testGetUsageHelp()
    {
        $result = $this->shell->getUsageHelp();
        $this->assertContains('Usage:  php -f', $result);
    }
}
