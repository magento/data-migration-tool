<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Record;

class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Record[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $records;

    /**
     * @var \Migration\ResourceModel\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    /**
     * @var \Migration\ResourceModel\Record\Collection
     */
    protected $recordCollection;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->structure = $this->createMock(\Migration\ResourceModel\Structure::class);
        $record1 = $this->createMock(\Migration\ResourceModel\Record::class);
        $record1->expects($this->any())->method('getValue')->with('fieldName')->willReturn('item1');
        $record2 = $this->createMock(\Migration\ResourceModel\Record::class);
        $record2->expects($this->any())->method('getValue')->with('fieldName')->willReturn('item2');
        $record3 = $this->createMock(\Migration\ResourceModel\Record::class);
        $record3->expects($this->any())->method('getValue')->with('fieldName')->willReturn('item3');
        $this->records = [$record1, $record2, $record3];
        $this->recordCollection = new \Migration\ResourceModel\Record\Collection($this->structure, $this->records);
    }

    /**
     * @return void
     */
    public function testGetStructure()
    {
        $this->assertSame($this->structure, $this->recordCollection->getStructure());
    }

    /**
     * @return void
     */
    public function testIterator()
    {
        $result = '';

        foreach ($this->recordCollection as $key => $record) {
            $result .= ' ' . $key . '=>' . $record->getValue('fieldName');
        }

        $this->assertEquals(' 0=>item1 1=>item2 2=>item3', $result);
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testAddRecord()
    {
        $this->assertEquals(3, count($this->recordCollection));
        $record = $this->createMock(\Migration\ResourceModel\Record::class);
        $record->expects($this->any())->method('getStructure')->willReturn($this->structure);
        $record->expects($this->any())->method('validateStructure')->with($this->equalTo($this->structure))
            ->willReturn(true);

        $this->recordCollection->addRecord($record);
        $this->assertEquals(4, count($this->recordCollection));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Record structure does not equal Collection structure
     * @return void
     */
    public function testAddRecordWithException()
    {
        $this->assertEquals(3, count($this->recordCollection));
        $record = $this->createMock(\Migration\ResourceModel\Record::class);
        $this->recordCollection->addRecord($record);
        $this->assertEquals(4, count($this->recordCollection));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testGetValue()
    {
        $this->structure->expects($this->any())->method('hasField')->with($this->equalTo('fieldName'))
            ->willReturn(true);
        $this->assertEquals(['item1', 'item2', 'item3'], $this->recordCollection->getValue('fieldName'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Collection Structure does not contain field fieldName
     * @return void
     */
    public function testGetValueWithException()
    {
        $this->assertEquals(['item1', 'item2', 'item3'], $this->recordCollection->getValue('fieldName'));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testSetValue()
    {
        $this->structure->expects($this->any())->method('hasField')->with($this->equalTo('fieldName'))
            ->willReturn(true);
        $this->records[0]->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('fieldName'), $this->equalTo('default'));
        $this->records[1]->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('fieldName'), $this->equalTo('default'));
        $this->records[2]->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('fieldName'), $this->equalTo('default'));
        $this->assertInstanceOf(
            \Migration\ResourceModel\Record\Collection::class,
            $this->recordCollection->setValue('fieldName', 'default')
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Collection Structure does not contain field fieldName
     * @return void
     */
    public function testSetValueWithException()
    {
        $this->recordCollection->setValue('fieldName', 'default');
    }
}
