<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Reader\Map;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;

/**
 * Class CustomerSegmentConditionSqlTest
 */
class CustomerSegmentConditionSqlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConditionSql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handler;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /** @var  Map|\PHPUnit_Framework_MockObject_MockObject */
    protected $mapMain;

    /** @var  Map|\PHPUnit_Framework_MockObject_MockObject */
    protected $mapSalesOrder;

    /**
     * @return void
     */
    public function setUp()
    {
        /** @var Map|\PHPUnit_Framework_MockObject_MockObject $map */
        $this->mapMain = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var Map|\PHPUnit_Framework_MockObject_MockObject $map */
        $this->mapSalesOrder = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->at(0))->method('create')->with('map_file')->willReturn($this->mapMain);
        $mapFactory->expects($this->at(1))->method('create')->with('sales_order_map_file')
            ->willReturn($this->mapSalesOrder);

        /** @var Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentList', 'addDocumentPrefix'])
            ->getMock();
        /** @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject $destination */
        $destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)->disableOriginalConstructor()
            ->setMethods(['addDocumentPrefix'])
            ->getMock();
        $destination->expects($this->any())->method('addDocumentPrefix')->will($this->returnCallback(function ($value) {
            return 'pfx_' . $value;
        }));

        $this->handler = new CustomerSegmentConditionSql($mapFactory, $this->source, $destination);
    }

    /**
     * @return void
     */
    public function testHandle()
    {
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)
            ->will($this->returnValue('SELECT * FROM `sales_flat_order` LEFT JOIN `source_some_document`'));
        $recordToHandle->expects($this->once())->method('setValue')
            ->with($fieldName, 'SELECT * FROM `pfx_sales_order` LEFT JOIN `pfx_dest_some_document`');

        $this->mapMain->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['source_some_document', MapInterface::TYPE_SOURCE, 'dest_some_document']
            ]
        );

        $this->mapSalesOrder->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['sales_flat_order', MapInterface::TYPE_SOURCE, 'sales_order']
            ]
        );

        $this->source->expects($this->once())->method('getDocumentList')
            ->will($this->returnValue(['sales_flat_order', 'source_some_document']));
        $this->source->expects($this->any())->method('addDocumentPrefix')->will($this->returnArgument(0));

        $this->handler->setField($fieldName);
        $this->handler->handle($recordToHandle, $oppositeRecord);
    }
}
