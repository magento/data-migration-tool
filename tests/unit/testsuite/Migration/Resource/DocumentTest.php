<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Record\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\Resource\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    /**
     * @var \Migration\Resource\Document
     */
    protected $document;

    protected function setUp()
    {
        $this->structure = $this->getMock(
            '\Migration\Resource\Structure',
            [],
            [],
            '',
            false
        );
        $this->recordCollectionFactory = $this->getMock(
            '\Migration\Resource\Record\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->document = new \Migration\Resource\Document(
            $this->recordCollectionFactory,
            $this->structure,
            'test_document'
        );
    }

    public function testGetRecords()
    {
        $recordCollection = $this->getMock(
            '\Migration\Resource\Record\RecordCollection',
            [],
            [],
            '',
            false
        );
        $this->recordCollectionFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with($this->equalTo([
                'structure' => $this->structure,
                'documentName' => 'test_document',
            ]))
            ->will($this->returnValue($recordCollection));

        $this->assertSame($recordCollection, $this->document->getRecords());
    }

    public function testGetName()
    {
        $this->assertEquals('test_document', $this->document->getName());
    }

    public function testGetStructure()
    {
        $this->assertSame($this->structure, $this->document->getStructure());
    }
}
