<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class AbstractCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Document\Collection
     */
    protected $documentCollection;

    /**
     * @var \Migration\Resource\Document[]
     */
    protected $data;

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

    public function testCurrent()
    {
        $this->assertSame($this->data[0], $this->documentCollection->current());
        $this->documentCollection->next();
        $this->assertSame($this->data[1], $this->documentCollection->current());
    }

    public function testKey()
    {
        $this->assertEquals(0, $this->documentCollection->key());
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->assertEquals(2, $this->documentCollection->key());
    }

    public function testNext()
    {
        $this->assertEquals(0, $this->documentCollection->key());
        $this->documentCollection->next();
        $this->assertEquals(1, $this->documentCollection->key());
    }

    public function testRewind()
    {
        $this->documentCollection->next();
        $this->assertEquals(1, $this->documentCollection->key());
        $this->documentCollection->rewind();
        $this->assertEquals(0, $this->documentCollection->key());
    }

    public function testValid()
    {
        $this->assertTrue($this->documentCollection->valid());
    }

    public function testNotValid()
    {
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->assertFalse($this->documentCollection->valid());
    }

    public function testCount()
    {
        $this->assertEquals(3, $this->documentCollection->count());
    }

    public function testIterator()
    {
        $result = '';
        foreach ($this->documentCollection as $key => $value) {
            $result .= ' ' . $key . '=>' . $value->getName();
        }

        $this->assertEquals(' 0=>Doc1 1=>Doc2 2=>Doc3', $result);
    }
}
