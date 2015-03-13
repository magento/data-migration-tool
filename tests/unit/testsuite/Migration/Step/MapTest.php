<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\MapReader\MapReaderMain;

/**
 * Class MapTest
 */
class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Integrity\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

    /**
     * @var Run\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $run;

    /**
     * @var Volume\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $volume;

    /**
     * @var Map
     */
    protected $map;

    public function setUp()
    {
        $this->integrity = $this->getMock('Migration\Step\Integrity\Map', ['perform'], [], '', false);
        $this->run = $this->getMock('Migration\Step\Run\Map', ['perform'], [], '', false);
        $this->volume = $this->getMock('Migration\Step\Volume\Map', ['perform'], [], '', false);
        $this->map = new Map(
            $this->integrity,
            $this->run,
            $this->volume
        );
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

    public function testGetTitle()
    {
        $this->assertEquals('Map step', $this->map->getTitle());
    }

    public function testRollback()
    {
        $this->assertTrue($this->map->rollback());
    }
}
