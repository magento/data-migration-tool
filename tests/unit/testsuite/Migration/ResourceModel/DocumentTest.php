<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\ResourceModel\Record\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\ResourceModel\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    /**
     * @var \Migration\ResourceModel\Document
     */
    protected $document;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->structure = $this->getMock(
            '\Migration\ResourceModel\Structure',
            [],
            [],
            '',
            false
        );
        $this->recordCollectionFactory = $this->getMock(
            '\Migration\ResourceModel\Record\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->document = new \Migration\ResourceModel\Document(
            $this->recordCollectionFactory,
            $this->structure,
            'test_document'
        );
    }

    /**
     * @return void
     */
    public function testGetRecords()
    {
        $recordCollection = $this->getMock(
            '\Migration\ResourceModel\Record\RecordCollection',
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

    /**
     * @return void
     */
    public function testGetName()
    {
        $this->assertEquals('test_document', $this->document->getName());
    }

    /**
     * @return void
     */
    public function testGetStructure()
    {
        $this->assertSame($this->structure, $this->document->getStructure());
    }
}
