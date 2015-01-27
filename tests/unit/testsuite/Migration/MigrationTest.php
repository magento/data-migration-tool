<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration;

class MigrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Migration
     */
    protected $migration;

    /**
     * @var \Migration\App\ShellFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shellFactory;

    /**
     * @var \Magento\Framework\App\Console\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    protected function setUp()
    {
        $writer = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $factory = $this->getMockBuilder('\Magento\Framework\Filesystem\Directory\WriteFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $writer->expects($this->any())->method('isDirectory')->will($this->returnValue(false));
        $writer->expects($this->any())->method('create')->will($this->returnValue(true));
        $factory->expects($this->any())->method('create')->will($this->returnValue($writer));

        $this->response = $this->getMock('\Magento\Framework\App\Console\Response', [], [], '', false);
        $this->shellFactory = $this->getMock('\Migration\App\ShellFactory', ['create'], [], '', false);
        $this->migration = new Migration($this->response, $this->shellFactory, $factory, basename(__FILE__));
    }

    public function testLaunch()
    {
        $shell = $this->getMock('\Migration\App\Shell', [], [], '', false);
        $shell->expects($this->any())
            ->method('run');
        $this->shellFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($shell));
        $result = $this->migration->launch();
        $this->assertEquals($this->response, $result);
    }

    public function testCatchException()
    {
        /** @var \Magento\Framework\App\Bootstrap|\PHPUnit_Framework_TestCase $bootstrap */
        $bootstrap = $this->getMock('Magento\Framework\App\Bootstrap', [], [], '', false);
        /** @var \Exception|\PHPUnit_Framework_TestCase $exception */
        $exception = $this->getMock('\Exception', [], [], '', false);
        $result = $this->migration->catchException($bootstrap, $exception);
        $this->assertFalse($result);
    }
}
