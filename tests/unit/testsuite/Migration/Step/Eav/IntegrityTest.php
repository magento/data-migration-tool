<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Eav\Integrity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

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
     * @var \Migration\MapReader\MapReaderSimpleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReaderSimple;

    /**
     * @var \Migration\MapReader\MapReaderEav|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    public function setUp()
    {
        $this->progress = $this->getMockBuilder('\Migration\ProgressBar')->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->source = $this->getMockBuilder('\Migration\Resource\Source')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->destination = $this->getMockBuilder('\Migration\Resource\Destination')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->mapReader = $this->getMockBuilder('\Migration\MapReader\MapReaderEav')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'getDocumentList', 'getFieldMap'])
            ->getMock();
        $this->mapReaderSimple = $this->getMockBuilder('\Migration\MapReader\MapReaderSimple')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $mapReaderSimpleFactory = $this->getMockBuilder('\Migration\MapReader\MapReaderSimpleFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $mapReaderSimpleFactory->expects($this->any())
            ->method('create')
            ->with(['optionName' => 'eav_list_file'])
            ->willReturn($this->mapReaderSimple);
        $this->integrity = new Integrity(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->mapReader,
            $mapReaderSimpleFactory
        );
    }

    public function testPerformWithoutError()
    {
        $fields = ['field1' => []];
        $this->mapReader->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['document_2', 'source', 'document_1'],
                ['document_1', 'destination', 'document_2']
            ]
        ) ;
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_2']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_1']));
        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));
        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->mapReader->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));
        $this->logger->expects($this->never())->method('error');
        $this->mapReaderSimple->expects($this->any())->method('getList')->with('documents')->willReturn(['document_2']);

        $this->assertTrue($this->integrity->perform());
    }

    public function testPerformWithError()
    {
        $fields = ['field1' => []];
        $this->mapReader->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['document_2', 'source', 'document_2'],
                ['document_1', 'destination', 'document_1']
            ]
        ) ;
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_2']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_1']));
        $this->mapReader->expects($this->atLeastOnce())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->exactly(2))->method('error');
        $this->mapReaderSimple->expects($this->any())->method('getList')->with('documents')->willReturn(['document_2']);

        $this->assertFalse($this->integrity->perform());
    }
}
