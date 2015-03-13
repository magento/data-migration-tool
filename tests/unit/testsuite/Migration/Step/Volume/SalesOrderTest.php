<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Volume;

use Migration\Logger\Logger;
use Migration\MapReader\MapReaderSalesOrder;
use Migration\Resource\Destination;
use Migration\Resource\Source;
use Migration\Step\SalesOrder\Helper;
use Migration\Step\SalesOrder\InitialData;
use Migration\ProgressBar;

class SalesOrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InitialData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initialData;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ProgressBar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var MapReaderSalesOrder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

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
     * @var SalesOrder
     */
    protected $salesOrder;

    public function setUp()
    {
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->progress = $this->getMock('\Migration\ProgressBar', ['start', 'finish', 'advance'], [], '', false);
        $this->initialData = $this->getMock(
            '\Migration\Step\SalesOrder\InitialData',
            ['getDestEavAttributesCount'],
            [],
            '',
            false
        );
        $this->helper = $this->getMock(
            '\Migration\Step\SalesOrder\Helper',
            ['getDocumentList', 'getDestEavDocument', 'getEavAttributes', 'getSourceAttributes'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\Resource\Source',
            ['getDocumentList', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\Resource\Destination',
            ['getRecordsCount'],
            [],
            '',
            false
        );
        $this->mapReader = $this->getMock('Migration\MapReader\MapReaderSalesOrder', ['getDocumentMap'], [], '', false);
        $this->salesOrder = new SalesOrder(
            $this->source,
            $this->destination,
            $this->initialData,
            $this->helper,
            $this->mapReader,
            $this->progress,
            $this->logger
        );
    }

    public function testPerform()
    {
        $sourceDocumentName = 'source_document';
        $destDocumentName = 'dest_document';
        $eavDocumentName = 'eav_entity_int';
        $eavAttributes = [
            'eav_attribute_1',
            'eav_attribute_2'
        ];
        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([$sourceDocumentName => $destDocumentName]);
        $this->helper->expects($this->any())->method('getEavAttributes')->willReturn($eavAttributes);
        $this->helper->expects($this->any())->method('getDestEavDocument')->willReturn($eavDocumentName);
        $this->helper->expects($this->at(3))->method('getSourceAttributes')->willReturn(1);
        $this->helper->expects($this->at(4))->method('getSourceAttributes')->willReturn(0);
        $this->progress->expects($this->any())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->mapReader->expects($this->any())->method('getDocumentMap')->with($sourceDocumentName)
            ->willReturn($destDocumentName);
        $this->source->expects($this->any())->method('getRecordsCount')->with($sourceDocumentName)->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->willReturnMap(
            [
                [$destDocumentName, 1],
                [$eavDocumentName, 1]
            ]
        );
        $this->initialData->expects($this->once())->method('getDestEavAttributesCount')->with('eav_entity_int')
            ->willReturn(0);
        $this->assertTrue($this->salesOrder->perform());
    }

    public function testPerformFailExistingEavAttributes()
    {
        $sourceDocumentName = 'source_document';
        $destDocumentName = 'dest_document';
        $eavDocumentName = 'eav_entity_int';
        $eavAttributes = [
            'eav_attribute_1',
            'eav_attribute_2'
        ];
        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([$sourceDocumentName => $destDocumentName]);
        $this->helper->expects($this->any())->method('getEavAttributes')->willReturn($eavAttributes);
        $this->helper->expects($this->any())->method('getDestEavDocument')->willReturn($eavDocumentName);
        $this->helper->expects($this->at(3))->method('getSourceAttributes')->willReturn(1);
        $this->helper->expects($this->at(4))->method('getSourceAttributes')->willReturn(0);
        $this->progress->expects($this->any())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->mapReader->expects($this->any())->method('getDocumentMap')->with($sourceDocumentName)
            ->willReturn($destDocumentName);
        $this->source->expects($this->any())->method('getRecordsCount')->with($sourceDocumentName)->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->willReturnMap(
            [
                [$destDocumentName, 1],
                [$eavDocumentName, 1]
            ]
        );
        $this->initialData->expects($this->once())->method('getDestEavAttributesCount')
            ->with('eav_entity_int')->willReturn(1);
        $this->logger->expects($this->once())->method('error')->with(
            PHP_EOL . 'Volume check failed for the destination document ' . $eavDocumentName
        );
        $this->assertFalse($this->salesOrder->perform());
    }

    public function testPerformFailExistingDocumentEntities()
    {
        $sourceDocumentName = 'source_document';
        $destDocumentName = 'dest_document';
        $eavDocumentName = 'eav_entity_int';
        $eavAttributes = [
            'eav_attribute_1',
            'eav_attribute_2'
        ];
        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([$sourceDocumentName => $destDocumentName]);
        $this->helper->expects($this->any())->method('getEavAttributes')->willReturn($eavAttributes);
        $this->helper->expects($this->any())->method('getDestEavDocument')->willReturn($eavDocumentName);
        $this->helper->expects($this->at(3))->method('getSourceAttributes')->willReturn(1);
        $this->helper->expects($this->at(4))->method('getSourceAttributes')->willReturn(0);
        $this->progress->expects($this->any())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->mapReader->expects($this->any())->method('getDocumentMap')->with($sourceDocumentName)
            ->willReturn($destDocumentName);
        $this->source->expects($this->any())->method('getRecordsCount')->with($sourceDocumentName)->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->willReturnMap(
            [
                [$destDocumentName, 2],
                [$eavDocumentName, 1]
            ]
        );
        $this->initialData->expects($this->once())->method('getDestEavAttributesCount')->with('eav_entity_int')
            ->willReturn(0);
        $this->logger->expects($this->once())->method('error')->with(
            PHP_EOL . 'Volume check failed for the destination document ' . $destDocumentName
        );
        $this->assertFalse($this->salesOrder->perform());
    }
}
