<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Handler;
use Migration\Reader\Map;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\RecordTransformerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordTransformerFactory;

    /**
     * ProgressBar instance
     *
     * @var ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var Data
     */
    protected $salesOrder;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance']
        );
        $this->source = $this->createPartialMock(
            \Migration\ResourceModel\Source::class,
            ['getDocument', 'getDocumentList', 'getRecords', 'getRecordsCount']
        );
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getDocument', 'getDocumentList', 'getRecords', 'saveRecords', 'clearDocument']
        );
        $this->recordFactory = $this->createPartialMock(
            \Migration\ResourceModel\RecordFactory::class,
            ['create']
        );
        $this->recordTransformerFactory = $this->createPartialMock(
            \Migration\RecordTransformerFactory::class,
            ['create']
        );
        $this->map = $this->createPartialMock(
            \Migration\Reader\Map::class,
            ['getDocumentMap']
        );

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('sales_order_map_file')->willReturn($this->map);

        $this->helper = $this->createPartialMock(
            \Migration\Step\SalesOrder\Helper::class,
            ['getDocumentList', 'getDestEavDocument', 'getEavAttributes']
        );

        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

        $this->salesOrder = new Data(
            $this->progress,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->recordTransformerFactory,
            $mapFactory,
            $this->helper,
            $this->logger
        );
    }

    /**
     * @covers \Migration\Step\SalesOrder\Data::prepareEavEntityData
     * @covers \Migration\Step\SalesOrder\Data::getAttributeData
     * @covers \Migration\Step\SalesOrder\Data::getAttributeValue
     * @covers \Migration\Step\SalesOrder\Data::getDestEavDocument
     * @return void
     */
    public function testGetMap()
    {
        $sourceDocumentName = 'source_document';
        $destinationDocumentName = 'destination_document';
        $eavAttributes = ['eav_attr_1', 'eav_attr_2'];
        $eavAttributeData = [
            'entity_type_id' => 1,
            'attribute_id' => 2,
            'attribute_code' => 'eav_attr_1'
        ];
        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([$sourceDocumentName => $destinationDocumentName]);
        $this->helper->expects($this->once())->method('getDestEavDocument')->willReturn('eav_document');
        $this->helper->expects($this->at(3))->method('getEavAttributes')->willReturn($eavAttributes);
        $this->map->expects($this->once())->method('getDocumentMap')
            ->willReturn($destinationDocumentName);
        $sourceDocument = $this->createPartialMock(
            \Migration\ResourceModel\Document::class,
            ['getRecords']
        );
        $this->source->expects($this->once())->method('getDocument')->willReturn($sourceDocument);
        $this->source->expects($this->any())->method('getRecordsCount')->willReturn(2);
        $destinationDocument = $this->createMock(\Migration\ResourceModel\Document::class);
        $eavDestinationDocument = $this->createMock(\Migration\ResourceModel\Document::class);
        $dstDocName = 'destination_document';
        $eavDstDocName = 'eav_document';
        $this->destination->expects($this->any())->method('getDocument')->willReturnMap(
            [
                [$dstDocName, $destinationDocument],
                [$eavDstDocName, $eavDestinationDocument]
            ]
        );
        $recordTransformer = $this->createPartialMock(
            \Migration\RecordTransformer::class,
            ['init', 'transform']
        );
        $this->recordTransformerFactory->expects($this->once())->method('create')->willReturn($recordTransformer);
        $recordTransformer->expects($this->once())->method('init');
        $bulk = [['eav_attr_1' => 'attribute_value', 'store_id' => '1', 'entity_id' => '2']];
        $this->source->expects($this->at(3))->method('getRecords')->willReturn($bulk);
        $this->source->expects($this->at(4))->method('getRecords')->willReturn([]);
        $destinationRecords =  $this->createMock(\Migration\ResourceModel\Record\Collection::class);
        $eavDestinationRecords = $this->createMock(\Migration\ResourceModel\Record\Collection::class);
        $destinationDocument->expects($this->once())->method('getRecords')->willReturn($destinationRecords);
        $srcRecord = $this->createMock(\Migration\ResourceModel\Record::class);
        $dstRecord = $this->createMock(\Migration\ResourceModel\Record::class);
        $this->recordFactory->expects($this->at(0))->method('create')->willReturn($srcRecord);
        $this->recordFactory->expects($this->at(1))->method('create')->willReturn($dstRecord);
        $recordTransformer->expects($this->once())->method('transform')->with($srcRecord, $dstRecord);
        $eavDestinationDocument->expects($this->once())->method('getRecords')->willReturn($eavDestinationRecords);
        $eavDestinationRecords->expects($this->once())->method('addRecord');
        $this->destination->expects($this->at(5))->method('saveRecords')->with($dstDocName, $destinationRecords);
        $this->destination->expects($this->at(6))->method('saveRecords')->with($eavDstDocName, $eavDestinationRecords);
        $this->destination->expects($this->once())->method('clearDocument')->with($dstDocName);
        $this->destination->expects($this->at(3))->method('getRecords')->willReturn([0 => $eavAttributeData]);
        $this->logger->expects($this->any())->method('debug')->with('migrating', ['table' => $sourceDocumentName])
            ->willReturn(true);
        $this->salesOrder->perform();
    }
}
