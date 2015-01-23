<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Record\RecordIteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordsIteratorFactory;

    /**
     * @var \Migration\Resource\Document\ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentProvider;

    /**
     * @var \Migration\Resource\Document\Document
     */
    protected $document;

    protected function setUp()
    {
        $this->documentProvider = $this->getMockForAbstractClass(
            '\Migration\Resource\Document\ProviderInterface',
            array(),
            '',
            false
        );
        $this->recordsIteratorFactory = $this->getMock(
            '\Migration\Resource\Record\RecordIteratorFactory',
            array(),
            array(),
            '',
            false
        );
        $this->document = new \Migration\Resource\Document\Document(
            $this->documentProvider,
            $this->recordsIteratorFactory,
            'test_document'
        );
    }

    public function testGetRecordsIterator()
    {
        $recordsIterator = $this->getMock(
            '\Migration\Resource\Record\RecordIterator',
            array(),
            array(),
            '',
            false
        );
        $this->recordsIteratorFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo(array(
                'documentName' => 'test_document',
            )))
            ->will($this->returnValue($recordsIterator));

        $this->assertSame($recordsIterator, $this->document->getRecordsIterator());
    }

    public function testGetName()
    {
        $this->assertEquals('test_document', $this->document->getName());
    }
}
