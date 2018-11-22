<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Reader\MapInterface;

class DeltaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
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
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var Delta|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $delta;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->source = $this->createMock(\Migration\ResourceModel\Source::class);
        $this->logger = $this->createMock(\Migration\Logger\Logger::class);
        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'getDeltaDocuments'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getAdapter']
        );
        $this->recordFactory = $this->createMock(\Migration\ResourceModel\RecordFactory::class);
        $this->recordTransformerFactory = $this->createMock(\Migration\RecordTransformerFactory::class);
        $this->data = $this->createMock(\Migration\Step\Map\Data::class);

        $this->readerGroups = $this->createPartialMock(
            \Migration\Reader\Groups::class,
            ['getGroup']
        );
        $this->readerGroups->expects($this->any())->method('getGroup')->willReturnMap(
            [
                ['delta_map', ['orders' => 'order_id']]
            ]
        );

        $this->helper = $this->createPartialMock(
            \Migration\Step\Map\Helper::class,
            ['getDocumentsDuplicateOnUpdate']
        );
        $this->helper->expects($this->any())->method('getDocumentsDuplicateOnUpdate')->willReturn(false);

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->createPartialMock(
            \Migration\Reader\GroupsFactory::class,
            ['create']
        );
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
            $this->data,
            $this->helper
        );
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
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
        $adapter = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Mysql::class,
            ['setForeignKeyChecks']
        );
        $adapter->expects($this->at(0))
            ->method('setForeignKeyChecks')
            ->with(1);
        $adapter->expects($this->at(1))
            ->method('setForeignKeyChecks')
            ->with(0);
        $this->destination->expects($this->any())
            ->method('getAdapter')
            ->willReturn($adapter);
        /** @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject $source */
        $document = $this->createMock(\Migration\ResourceModel\Document::class);
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
