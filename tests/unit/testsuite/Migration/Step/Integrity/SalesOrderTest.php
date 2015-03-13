<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

use Migration\MapReader\MapReaderSalesOrder;

/**
 * Class IntegrityTest
 */
class SalesOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var SalesOrder
     */
    protected $salesOrder;

    /**
     * @var MapReaderSalesOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Step\SalesOrder\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    public function setUp()
    {
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->source = $this->getMock('\Migration\Resource\Source', ['getDocumentList', 'getDocument'], [], '', false);
        $this->progress = $this->getMock('\Migration\ProgressBar', ['start', 'finish', 'advance'], [], '', false);
        $this->helper = $this->getMock(
            '\Migration\Step\SalesOrder\Helper',
            ['getDocumentList', 'getEavAttributes', 'getDestEavDocument'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            '\Migration\Resource\Destination',
            ['getDocumentList', 'getDocument', 'getRecords'],
            [],
            '',
            false
        );
        $this->mapReader = $this->getMockBuilder('\Migration\MapReader\MapReaderSalesOrder')
            ->disableOriginalConstructor()
            ->setMethods(['getFieldMap', 'getDocumentMap'])
            ->getMock();
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->salesOrder = new SalesOrder(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->mapReader,
            $this->helper
        );
    }

    /**
     * @covers \Migration\Step\Integrity\SalesOrder::checkEavEntities
     * @covers \Migration\Step\Integrity\SalesOrder::getEavEntities
     * @covers \Migration\Step\Integrity\SalesOrder::getIterationsCount
     */
    public function testPerform()
    {
        $fields = ['field1' => []];
        $destinationRecord = ['attribute_code' => 'eav_entity'];
        $this->helper->expects($this->any())->method('getDocumentList')->willReturn(['source_doc' => 'dest_doc']);
        $this->helper->expects($this->once())->method('getDestEavDocument')->willReturn('eav_entity_int');
        $this->helper->expects($this->once())->method('getEavAttributes')->willReturn(['eav_entity']);
        $this->progress->expects($this->once())->method('start')->with(3);
        $this->progress->expects($this->any())->method('advance');
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->willReturn($fields);
        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $this->source->expects($this->any())->method('getDocumentList')->willReturn(['source_doc']);
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['dest_doc']);
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);
        $this->destination->expects($this->at(3))->method('getRecords')->willReturn([0 => $destinationRecord]);
        $this->destination->expects($this->at(4))->method('getRecords')->willReturn(null);
        $this->mapReader->expects($this->at(0))->method('getDocumentMap')->willReturn('dest_doc');
        $this->mapReader->expects($this->at(2))->method('getDocumentMap')->willReturn('source_doc');
        $this->mapReader->expects($this->any())->method('getFieldMap')->willReturn('field1');
        $this->source->expects($this->any())->method('getDocument')->willReturn($document);

        $this->logger->expects($this->never())->method('error');

        $this->salesOrder->perform();
    }
}
