<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Reader\MapInterface;

class DeltaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Reader\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var \Migration\RecordTransformerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordTransformerFactory;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $data;

    /**
     * @var Delta|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $delta;

    public function setUp()
    {
        $this->source = $this->getMock('\Migration\Resource\Source', [], [], '', false);
        $this->logger = $this->getMock('\Migration\Logger\Logger', [], [], '', false);
        $this->map = $this->getMock('\Migration\Reader\Map', [], [], '', false);

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $this->destination = $this->getMock('\Migration\Resource\Destination', [], [], '', false);
        $this->recordFactory = $this->getMock('\Migration\Resource\RecordFactory', [], [], '', false);
        $this->recordTransformerFactory = $this->getMock('\Migration\RecordTransformerFactory', [], [], '', false);
        $this->data = $this->getMock('\Migration\Step\Map\Data', [], [], '', false);

        $this->readerGroups = $this->getMock('\Migration\Reader\Groups', ['getGroup'], [], '', false);
        $this->readerGroups->expects($this->any())->method('getGroup')->willReturnMap(
            [
                ['delta_map', ['orders' => 'order_id']]
            ]
        );

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMock('\Migration\Reader\GroupsFactory', ['create'], [], '', false);
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('delta_document_groups_file')
            ->willReturn($this->readerGroups);

        $this->delta = new Delta(
            $this->source,
            $mapFactory,
            $groupsFactory,
            $this->logger,
            $this->destination,
            $this->recordFactory,
            $this->recordTransformerFactory,
            $this->data
        );
    }

    public function testDelta()
    {
        $sourceDocName = 'orders';
        $sourceDeltaName = 'm2_cl_orders';
        $this->source->expects($this->any())
            ->method('getDocumentList')
            ->willReturn([$sourceDocName, $sourceDeltaName]);
        $this->source->expects($this->atLeastOnce())
            ->method('getDeltaLogName')
            ->with('orders')
            ->willReturn($sourceDeltaName);
        $this->source->expects($this->any())
            ->method('getRecordsCount')
            ->with($sourceDeltaName)
            ->willReturn(1);
        /** @var \Migration\Resource\Document|\PHPUnit_Framework_MockObject_MockObject $source */
        $document = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $this->source->expects($this->any())
            ->method('getDocument')
            ->willReturn($document);

        $this->map->expects($this->any())
            ->method('getDeltaDocuments')
            ->willReturn([$sourceDocName => 'order_id']);
        $this->map->expects($this->any())
            ->method('getDocumentMap')
            ->with($sourceDocName, MapInterface::TYPE_SOURCE)
            ->willReturn($sourceDocName);

        $this->logger->expects($this->any())
            ->method('debug')
            ->with($sourceDocName . ' has changes');

        $this->assertTrue($this->delta->perform());
    }
}
