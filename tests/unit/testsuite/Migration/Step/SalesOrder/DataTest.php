<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Handler;
use Migration\Reader\Map;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;

class DataTest extends \PHPUnit_Framework_TestCase
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
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\ResourceModel\Source',
            ['getDocument', 'getDocumentList', 'getRecords', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getDocument', 'getDocumentList', 'getRecords', 'saveRecords', 'clearDocument'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('Migration\ResourceModel\RecordFactory', ['create'], [], '', false);
        $this->recordTransformerFactory = $this->getMock(
            'Migration\RecordTransformerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->map = $this->getMock('Migration\Reader\Map', ['getDocumentMap'], [], '', false);

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('sales_order_map_file')->willReturn($this->map);

        $this->helper = $this->getMock(
            'Migration\Step\SalesOrder\Helper',
            ['getDocumentList', 'getDestEavDocument', 'getEavAttributes'],
            [],
            '',
            false
        );

        $this->logger = $this->getMockBuilder('Migration\Logger\Logger')->disableOriginalConstructor()
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
        $sourceDocument = $this->getMock('\Migration\ResourceModel\Document', ['getRecords'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->willReturn($sourceDocument);
        $this->source->expects($this->any())->method('getRecordsCount')->willReturn(2);
        $destinationDocument = $this->getMock('\Migration\ResourceModel\Document', [], [], '', false);
        $eavDestinationDocument = $this->getMock('\Migration\ResourceModel\Document', [], [], '', false);
        $dstDocName = 'destination_document';
        $eavDstDocName = 'eav_document';
        $this->destination->expects($this->any())->method('getDocument')->willReturnMap(
            [
                [$dstDocName, $destinationDocument],
                [$eavDstDocName, $eavDestinationDocument]
            ]
        );
        $recordTransformer = $this->getMock(
            'Migration\RecordTransformer',
            ['init', 'transform'],
            [],
            '',
            false
        );
        $this->recordTransformerFactory->expects($this->once())->method('create')->willReturn($recordTransformer);
        $recordTransformer->expects($this->once())->method('init');
        $bulk = [['eav_attr_1' => 'attribute_value', 'store_id' => '1', 'entity_id' => '2']];
        $this->source->expects($this->at(3))->method('getRecords')->willReturn($bulk);
        $this->source->expects($this->at(4))->method('getRecords')->willReturn([]);
        $destinationRecords =  $this->getMock('\Migration\ResourceModel\Record\Collection', [], [], '', false);
        $eavDestinationRecords = $this->getMock('\Migration\ResourceModel\Record\Collection', [], [], '', false);
        $destinationDocument->expects($this->once())->method('getRecords')->willReturn($destinationRecords);
        $srcRecord = $this->getMock('\Migration\ResourceModel\Record', [], [], '', false);
        $dstRecord = $this->getMock('\Migration\ResourceModel\Record', [], [], '', false);
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
