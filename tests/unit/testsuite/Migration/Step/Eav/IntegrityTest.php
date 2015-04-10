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
     * @var \Migration\Step\Eav\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

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
            ->setMethods(['getDocumentsMap', 'getDocumentMap', 'getDocumentList', 'getFieldMap'])
            ->getMock();
        $this->helper = $this->getMockBuilder('\Migration\Step\Eav\Helper')->disableOriginalConstructor()
            ->setMethods(['getDocuments'])
            ->getMock();
        $this->integrity = new Integrity(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->mapReader,
            $this->helper
        );
    }

    public function testPerformWithoutError()
    {
        $fields = ['field1' => []];
        $this->helper->expects($this->any())->method('getDocuments')->willReturn(['source_document']) ;
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods(['getFields'])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['source_document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['destination_document']));

        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->mapReader->expects($this->any())->method('getDocumentMap')->willReturnMap([
            ['source_document', 'source', 'destination_document'],
            ['destination_document', 'destination', 'source_document']
        ]);
        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->mapReader->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));
        $this->logger->expects($this->never())->method('error');

        $this->assertTrue($this->integrity->perform());
    }

    public function testPerformWithMissingDocuments()
    {
        $fields = ['field1' => []];
        $this->helper->expects($this->any())->method('getDocuments')->willReturn(['missed_source_document']) ;
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods(['getFields'])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['some_document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['destination_document']));

        $this->mapReader->expects($this->atLeastOnce())->method('getDocumentMap')->will($this->returnArgument(0));

        $this->logger->expects($this->exactly(2))->method('error');
        $this->assertFalse($this->integrity->perform());
    }
}
