<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Map\Integrity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

    /**
     * @var Map\Run|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $run;

    /**
     * @var Map\Volume|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $volume;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->integrity = $this->getMock('Migration\Step\Map\Integrity', ['perform'], [], '', false);
        $this->run = $this->getMock('Migration\Step\Map\Run', ['perform'], [], '', false);
        $this->volume = $this->getMock('Migration\Step\Map\Volume', ['perform'], [], '', false);
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->map = new Map($this->integrity, $this->run, $this->volume, $this->logger);
    }

    public function testIntegrity()
    {
        $this->integrity->expects($this->once())->method('perform');
        $this->map->integrity();
    }

    public function testRun()
    {
        $this->run->expects($this->once())->method('perform');
        $this->map->run();
    }

    public function testVolume()
    {
        $this->volume->expects($this->once())->method('perform');
        $this->map->volumeCheck();
    }

    public function testIntegrityException()
    {
        $exception = $this->getMock('\Exception', ['getMessage'], [], '', false);
        $this->integrity->expects($this->once())->method('perform')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error');
        $this->assertFalse($this->map->integrity());
    }

    public function testRunException()
    {
        $exception = $this->getMock('\Exception', ['getMessage'], [], '', false);
        $this->run->expects($this->once())->method('perform')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error');
        $this->map->run();
    }

    public function testVolumeCheck()
    {
        $exception = $this->getMock('\Exception', ['getMessage'], [], '', false);
        $this->volume->expects($this->once())->method('perform')->willThrowException($exception);
        $this->logger->expects($this->once())->method('error');
        $this->map->volumeCheck();
    }

    public function testGetTitle()
    {
        $this->assertEquals('Map step', $this->map->getTitle());
    }
}
