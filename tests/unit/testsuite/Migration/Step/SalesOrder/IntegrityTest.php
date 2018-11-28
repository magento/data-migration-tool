<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\Reader\Map;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Integrity
     */
    protected $salesOrder;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Step\SalesOrder\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['debug', 'addRecord', 'error']
        );
        $this->source = $this->createPartialMock(
            \Migration\ResourceModel\Source::class,
            ['getDocumentList', 'getDocument']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance']
        );
        $this->helper = $this->createPartialMock(
            \Migration\Step\SalesOrder\Helper::class,
            ['getDocumentList', 'getEavAttributes', 'getDestEavDocument']
        );
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getDocumentList', 'getDocument', 'getRecords']
        );
        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFieldMap', 'getDocumentMap', 'isDocumentIgnored'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('sales_order_map_file')->willReturn($this->map);

        $this->config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()
            ->setMethods([])->getMock();

        $config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()->getMock();
        $this->salesOrder = new Integrity(
            $this->progress,
            $this->logger,
            $config,
            $this->source,
            $this->destination,
            $mapFactory,
            $this->helper
        );
    }

    /**
     * @covers \Migration\Step\SalesOrder\Integrity::checkEavEntities
     * @covers \Migration\Step\SalesOrder\Integrity::getEavEntities
     * @covers \Migration\Step\SalesOrder\Integrity::getIterationsCount
     * @return void
     */
    public function testPerform()
    {
        $fields = ['field1' => ['DATA_TYPE' => 'int']];
        $destinationRecord = ['attribute_code' => 'eav_entity'];
        $this->helper->expects($this->any())->method('getDocumentList')->willReturn(['source_doc' => 'dest_doc']);
        $this->helper->expects($this->once())->method('getDestEavDocument')->willReturn('eav_entity_int');
        $this->helper->expects($this->once())->method('getEavAttributes')->willReturn(['eav_entity']);
        $this->progress->expects($this->once())->method('start')->with(3);
        $this->progress->expects($this->any())->method('advance');
        $structure = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->willReturn($fields);
        $document = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $this->source->expects($this->any())->method('getDocumentList')->willReturn(['source_doc']);
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['dest_doc']);
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);
        $this->destination->expects($this->at(3))->method('getRecords')->willReturn([0 => $destinationRecord]);
        $this->destination->expects($this->at(4))->method('getRecords')->willReturn(null);
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);
        $this->map->expects($this->at(1))->method('getDocumentMap')->willReturn('dest_doc');
        $this->map->expects($this->at(4))->method('getDocumentMap')->willReturn('source_doc');
        $this->map->expects($this->any())->method('getFieldMap')->willReturn('field1');
        $this->source->expects($this->any())->method('getDocument')->willReturn($document);

        $this->logger->expects($this->never())->method('error');

        $this->salesOrder->perform();
    }
}
