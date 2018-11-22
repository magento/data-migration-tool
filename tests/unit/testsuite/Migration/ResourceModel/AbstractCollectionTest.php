<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Document;

class AbstractCollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Document\Collection
     */
    protected $documentCollection;

    /**
     * @var \Migration\ResourceModel\Document[]
     */
    protected $data;

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
    public function testCurrent()
    {
        $this->assertSame($this->data[0], $this->documentCollection->current());
        $this->documentCollection->next();
        $this->assertSame($this->data[1], $this->documentCollection->current());
    }

    /**
     * @return void
     */
    public function testKey()
    {
        $this->assertEquals(0, $this->documentCollection->key());
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->assertEquals(2, $this->documentCollection->key());
    }

    /**
     * @return void
     */
    public function testNext()
    {
        $this->assertEquals(0, $this->documentCollection->key());
        $this->documentCollection->next();
        $this->assertEquals(1, $this->documentCollection->key());
    }

    /**
     * @return void
     */
    public function testRewind()
    {
        $this->documentCollection->next();
        $this->assertEquals(1, $this->documentCollection->key());
        $this->documentCollection->rewind();
        $this->assertEquals(0, $this->documentCollection->key());
    }

    /**
     * @return void
     */
    public function testValid()
    {
        $this->assertTrue($this->documentCollection->valid());
    }

    /**
     * @return void
     */
    public function testNotValid()
    {
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->documentCollection->next();
        $this->assertFalse($this->documentCollection->valid());
    }

    /**
     * @return void
     */
    public function testCount()
    {
        $this->assertEquals(3, $this->documentCollection->count());
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
}
