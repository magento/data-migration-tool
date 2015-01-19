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

    /**
     * @dataProvider runDataProvider
     * @param array $args
     * @param string $outputContains
     */
    public function testRun($args, $outputContains)
    {
        $this->shell->setRawArgs($args);
        ob_start();
        $result = $this->shell->run();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertSame($this->shell, $result);
        $this->assertContains($outputContains, $output);
    }

    public function runDataProvider()
    {
        return array(
            array(['--config', 'file/to/config.xml'], 'file/to/config.xml'),
            array(['--type', 'mapStep'], 'mapStep'),
            array(['--help'], 'Usage:  php -f')
        );
    }

    public function testGetUsageHelp()
    {
        $result = $this->shell->getUsageHelp();
        $this->assertContains('Usage:  php -f', $result);
    }
}
