<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\MapReader\MapReaderLog;

/**
 * Class LogTest
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Log\Integrity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

    /**
     * @var Log\Migrate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $run;

    /**
     * @var Log\Volume|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $volume;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var Log
     */
    protected $log;

    public function setUp()
    {
        $this->integrity = $this->getMock('Migration\Step\Log\Integrity', ['perform'], [], '', false);
        $this->run = $this->getMock('Migration\Step\Log\Migrate', ['perform'], [], '', false);
        $this->volume = $this->getMock('Migration\Step\Log\Volume', ['perform'], [], '', false);
        $this->log = new Log(
            $this->integrity,
            $this->run,
            $this->volume
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

    public function testGetTitle()
    {
        $this->assertEquals('Log Step', $this->log->getTitle());
    }

    public function testRollback()
    {
        $this->assertTrue($this->log->rollback());
    }
}
