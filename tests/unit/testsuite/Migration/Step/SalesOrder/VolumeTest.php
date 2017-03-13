<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\App\ProgressBar;

class VolumeTest extends \PHPUnit_Framework_TestCase
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
     * @var ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

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
     * @var Volume
     */
    protected $salesOrder;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->getMock('Migration\Logger\Logger', ['addRecord'], [], '', false);
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
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
            'Migration\ResourceModel\Source',
            ['getDocumentList', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getRecordsCount'],
            [],
            '',
            false
        );
        $this->map = $this->getMock('Migration\Reader\Map', ['getDocumentMap'], [], '', false);

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('sales_order_map_file')->willReturn($this->map);

        $this->salesOrder = new Volume(
            $this->source,
            $this->destination,
            $this->initialData,
            $this->helper,
            $mapFactory,
            $this->progress,
            $this->logger
        );
    }

    /**
     * @return void
     */
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
        $this->map->expects($this->any())->method('getDocumentMap')->with($sourceDocumentName)
            ->willReturn($destDocumentName);
        $this->source->expects($this->any())->method('getRecordsCount')->with($sourceDocumentName)->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->willReturnMap(
            [
                [$destDocumentName, true, [], 1],
                [$eavDocumentName, true, [], 1]
            ]
        );
        $this->initialData->expects($this->once())->method('getDestEavAttributesCount')->with('eav_entity_int')
            ->willReturn(0);
        $this->assertTrue($this->salesOrder->perform());
    }

    /**
     * @return void
     */
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
        $this->map->expects($this->any())->method('getDocumentMap')->with($sourceDocumentName)
            ->willReturn($destDocumentName);
        $this->source->expects($this->any())->method('getRecordsCount')->with($sourceDocumentName)->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->willReturnMap(
            [
                [$destDocumentName, true, [], 1],
                [$eavDocumentName, true, [], 1]
            ]
        );
        $this->initialData->expects($this->once())->method('getDestEavAttributesCount')
            ->with('eav_entity_int')->willReturn(1);
        $this->logger->expects($this->once())->method('addRecord')->with(
            Logger::WARNING,
            'Mismatch of entities in the document: ' . $eavDocumentName
        );
        $this->assertFalse($this->salesOrder->perform());
    }

    /**
     * @return void
     */
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
        $this->map->expects($this->any())->method('getDocumentMap')->with($sourceDocumentName)
            ->willReturn($destDocumentName);
        $this->source->expects($this->any())->method('getRecordsCount')->with($sourceDocumentName)->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->willReturnMap(
            [
                [$destDocumentName, true, [], 2],
                [$eavDocumentName, true, [], 1]
            ]
        );
        $this->initialData->expects($this->once())->method('getDestEavAttributesCount')->with('eav_entity_int')
            ->willReturn(0);
        $this->logger->expects($this->once())->method('addRecord')->with(
            Logger::WARNING,
            'Mismatch of entities in the document: ' . $destDocumentName
        );
        $this->assertFalse($this->salesOrder->perform());
    }
}
