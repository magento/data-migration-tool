<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var []
     */
    protected $data;

    /**
     * @var \Migration\Resource\Document\Collection
     */
    protected $documentCollection;

    protected function setUp()
    {
        $document1 = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $document1->expects($this->any())->method('getName')->will($this->returnValue('Doc1'));
        $document2 = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $document2->expects($this->any())->method('getName')->will($this->returnValue('Doc2'));
        $document3 = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $document3->expects($this->any())->method('getName')->will($this->returnValue('Doc3'));
        $this->data = [$document1, $document2, $document3];
        $this->documentCollection = new \Migration\Resource\Document\Collection($this->data);
    }

    public function testIterator()
    {
        $result = '';
        foreach ($this->documentCollection as $key => $value) {
            $result .= ' ' . $key . '=>' . $value->getName();
        }

        $this->assertEquals(' 0=>Doc1 1=>Doc2 2=>Doc3', $result);
    }

    public function testGetDocument()
    {
        $this->assertSame($this->data[2], $this->documentCollection->getDocument('Doc3'));
    }

    public function testGetDocumentNotExists()
    {
        $this->assertNull($this->documentCollection->getDocument('Doc5'));
    }

    public function testAddDocument()
    {
        $document = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $document->expects($this->any())->method('getName')->will($this->returnValue('Doc4'));
        $this->documentCollection->addDocument($document);
        $this->assertSame($document, $this->documentCollection->getDocument('Doc4'));
    }
}
