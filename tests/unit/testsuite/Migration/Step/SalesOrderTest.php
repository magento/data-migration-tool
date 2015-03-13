<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\MapReader;

/**
 * Class SalesOrderTest
 */
class SalesOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Integrity\SalesOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

    /**
     * @var Run\SalesOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $run;

    /**
     * @var Volume\SalesOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $volume;

    /**
     * @var SalesOrder
     */
    protected $salesOrder;

    /**
     * @var SalesOrder\InitialData
     */
    protected $initialData;

    public function setUp()
    {
        $this->integrity = $this->getMock('Migration\Step\Integrity\SalesOrder', ['perform'], [], '', false);
        $this->run = $this->getMock('Migration\Step\Run\SalesOrder', ['perform'], [], '', false);
        $this->volume = $this->getMock('Migration\Step\Volume\SalesOrder', ['perform'], [], '', false);
        $this->initialData = $this->getMock('Migration\Step\SalesOrder\InitialData', ['init'], [], '', false);
        $this->salesOrder = new SalesOrder(
            $this->integrity,
            $this->run,
            $this->volume,
            $this->initialData
        );
    }

    public function testIntegrity()
    {
        $this->integrity->expects($this->once())->method('perform');
        $this->salesOrder->integrity();
    }

    public function testRun()
    {
        $this->run->expects($this->once())->method('perform');
        $this->salesOrder->run();
    }

    public function testVolume()
    {
        $this->volume->expects($this->once())->method('perform');
        $this->salesOrder->volumeCheck();
    }

    public function testGetTitle()
    {
        $this->assertEquals('SalesOrder step', $this->salesOrder->getTitle());
    }
}
