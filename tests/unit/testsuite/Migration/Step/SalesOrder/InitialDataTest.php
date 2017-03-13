<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Reader;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

/**
 * Class InitialDataTest
 */
class InitialDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var InitialData
     */
    protected $initialData;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->source = $this->getMock('\Migration\ResourceModel\Source', [], [], '', false);
        $this->destination = $this->getMock('\Migration\ResourceModel\Destination', ['getRecordsCount'], [], '', false);
        $this->helper = $this->getMock('\Migration\Step\SalesOrder\Helper', ['getDestEavDocument'], [], '', false);
        $this->initialData = new InitialData($this->source, $this->destination, $this->helper);
    }

    /**
     * @covers \Migration\Step\SalesOrder\InitialData::initDestAttributes
     * @covers \Migration\Step\SalesOrder\InitialData::getDestEavAttributesCount
     * @return void
     */
    public function testInit()
    {
        $eavEntityDocument = 'eav_entity_int';
        $this->helper->expects($this->once())->method('getDestEavDocument')->willReturn($eavEntityDocument);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(2);
        $this->initialData->init();
        $this->assertEquals($this->initialData->getDestEavAttributesCount($eavEntityDocument), 2);
    }
}
