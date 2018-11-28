<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\App\ProgressBar;

class VolumeTest extends \PHPUnit\Framework\TestCase
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
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['addRecord']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance']
        );
        $this->initialData = $this->createPartialMock(
            \Migration\Step\SalesOrder\InitialData::class,
            ['getDestEavAttributesCount']
        );
        $this->helper = $this->createPartialMock(
            \Migration\Step\SalesOrder\Helper::class,
            ['getDocumentList', 'getDestEavDocument', 'getEavAttributes', 'getSourceAttributes']
        );
        $this->source = $this->createPartialMock(
            \Migration\ResourceModel\Source::class,
            ['getDocumentList', 'getRecordsCount']
        );
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getRecordsCount']
        );
        $this->map = $this->createPartialMock(
            \Migration\Reader\Map::class,
            ['getDocumentMap']
        );

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
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
            'Mismatch of entities in the document: ' . $destDocumentName . ' Source: 1 Destination: 2'
        );
        $this->assertFalse($this->salesOrder->perform());
    }
}
