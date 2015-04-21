<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

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
     * @var \Migration\MapReader\MapReaderMain|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

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
        $this->mapReader = $this->getMock('\Migration\MapReader\MapReaderMain', [], [], '', false);
        $this->destination = $this->getMock('\Migration\Resource\Destination', [], [], '', false);
        $this->recordFactory = $this->getMock('\Migration\Resource\RecordFactory', [], [], '', false);
        $this->recordTransformerFactory = $this->getMock('\Migration\RecordTransformerFactory', [], [], '', false);
        $this->data = $this->getMock('\Migration\Step\Map\Data', [], [], '', false);

        $this->delta = new Delta(
            $this->source,
            $this->mapReader,
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
            ->with($sourceDeltaName, false)
            ->willReturn(1);

        $this->mapReader->expects($this->any())
            ->method('getDeltaDocuments')
            ->willReturn([$sourceDocName => 'order_id']);
        $this->mapReader->expects($this->any())
            ->method('getDocumentMap')
            ->with($sourceDocName, 'source')
            ->willReturn($sourceDocName);

        $this->logger->expects($this->any())
            ->method('debug')
            ->with(PHP_EOL . $sourceDocName . ' has changes');

        $this->assertTrue($this->delta->perform());
    }
}
