<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Document;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var []
     */
    protected $data;

    /**
     * @var \Migration\ResourceModel\Document\Collection
     */
    protected $documentCollection;

    /**
     * @return void
     */
    protected function setUp()
    {
        $document1 = $this->createMock(\Migration\ResourceModel\Document::class);
        $document1->expects($this->any())->method('getName')->will($this->returnValue('Doc1'));
        $document2 = $this->createMock(\Migration\ResourceModel\Document::class);
        $document2->expects($this->any())->method('getName')->will($this->returnValue('Doc2'));
        $document3 = $this->createMock(\Migration\ResourceModel\Document::class);
        $document3->expects($this->any())->method('getName')->will($this->returnValue('Doc3'));
        $this->data = [$document1, $document2, $document3];
        $this->documentCollection = new \Migration\ResourceModel\Document\Collection($this->data);
    }

    /**
     * @return void
     */
    public function testIterator()
    {
        $result = '';
        foreach ($this->documentCollection as $key => $value) {
            $result .= ' ' . $key . '=>' . $value->getName();
        }

        $this->assertEquals(' 0=>Doc1 1=>Doc2 2=>Doc3', $result);
    }

    /**
     * @return void
     */
    public function testGetDocument()
    {
        $this->assertSame($this->data[2], $this->documentCollection->getDocument('Doc3'));
    }

    /**
     * @return void
     */
    public function testGetDocumentNotExists()
    {
        $this->assertNull($this->documentCollection->getDocument('Doc5'));
    }

    /**
     * @return void
     */
    public function testAddDocument()
    {
        $document = $this->createMock(\Migration\ResourceModel\Document::class);
        $document->expects($this->any())->method('getName')->will($this->returnValue('Doc4'));
        $this->documentCollection->addDocument($document);
        $this->assertSame($document, $this->documentCollection->getDocument('Doc4'));
    }
}
