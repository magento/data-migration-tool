<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Integrity\Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

    /**
     * @var Run\Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $run;

    /**
     * @var Volume\Log|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $volume;

    /**
     * @var \Migration\MapReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->integrity = $this->getMock('Migration\Step\Integrity\Log', ['perform'], [], '', false);
        $this->run = $this->getMock('Migration\Step\Run\Log', ['perform'], [], '', false);
        $this->volume = $this->getMock('Migration\Step\Volume\Log', ['perform'], [], '', false);
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->mapReader = $this->getMock('Migration\MapReader', ['getDocumentMap', 'init'], [], '', false);
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->log = new Log(
            $this->integrity,
            $this->run,
            $this->volume,
            $this->mapReader,
            $this->config,
            $this->logger
        );
    }

    public function testIntegrity()
    {
        $this->integrity->expects($this->once())->method('perform');
        $this->log->integrity();
    }

    public function testRun()
    {
        $this->run->expects($this->once())->method('perform');
        $this->log->run();
    }

    public function testVolume()
    {
        $this->volume->expects($this->once())->method('perform');
        $this->log->volumeCheck();
    }

    public function testIntegrityException()
    {
        $exception = $this->getMock('\Exception', ['getMessage'], [], '', false);
        $this->integrity->expects($this->once())->method('perform')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error');
        $this->assertFalse($this->log->integrity());
    }

    public function testRunException()
    {
        $exception = $this->getMock('\Exception', ['getMessage'], [], '', false);
        $this->run->expects($this->once())->method('perform')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error');
        $this->log->run();
    }

    public function testVolumeCheckException()
    {
        $exception = $this->getMock('\Exception', ['getMessage'], [], '', false);
        $this->volume->expects($this->once())->method('perform')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error');
        $this->log->volumeCheck();
    }

    public function testGetTitle()
    {
        $this->assertEquals('Log Step', $this->log->getTitle());
    }
}
