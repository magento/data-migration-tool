<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Record[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $records;

    /**
     * @var \Migration\Resource\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    /**
     * @var \Migration\Resource\Record\Collection
     */
    protected $recordCollection;

    protected function setUp()
    {
        $this->structure = $this->getMock(
            '\Migration\Resource\Structure',
            [],
            [],
            '',
            false
        );
        $record1 = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record1->expects($this->any())->method('getValue')->with('fieldName')->willReturn('item1');
        $record2 = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record2->expects($this->any())->method('getValue')->with('fieldName')->willReturn('item2');
        $record3 = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record3->expects($this->any())->method('getValue')->with('fieldName')->willReturn('item3');
        $this->records = [$record1, $record2, $record3];
        $this->recordCollection = new \Migration\Resource\Record\Collection($this->structure, $this->records);
    }

    public function testGetStructure()
    {
        $this->assertSame($this->structure, $this->recordCollection->getStructure());
    }

    public function testIterator()
    {
        $result = '';

        foreach ($this->recordCollection as $key => $record) {
            $result .= ' ' . $key . '=>' . $record->getValue('fieldName');
        }

        $this->assertEquals(' 0=>item1 1=>item2 2=>item3', $result);
    }

    public function testAddRecord()
    {
        $this->assertEquals(3, count($this->recordCollection));
        $record = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record->expects($this->any())->method('getStructure')->willReturn($this->structure);
        $record->expects($this->any())->method('validateStructure')->with($this->equalTo($this->structure))
            ->willReturn(true);

        $this->recordCollection->addRecord($record);
        $this->assertEquals(4, count($this->recordCollection));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Record structure does not equal Collection structure
     */
    public function testAddRecordWithException()
    {
        $this->assertEquals(3, count($this->recordCollection));
        $record = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordCollection->addRecord($record);
        $this->assertEquals(4, count($this->recordCollection));
    }

    public function testGetValue()
    {
        $this->structure->expects($this->any())->method('hasField')->with($this->equalTo('fieldName'))
            ->willReturn(true);
        $this->assertEquals(['item1', 'item2', 'item3'], $this->recordCollection->getValue('fieldName'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Collection Structure does not contain field fieldName
     */
    public function testGetValueWithException()
    {
        $this->assertEquals(['item1', 'item2', 'item3'], $this->recordCollection->getValue('fieldName'));
    }

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
        $this->recordCollection->setValue('fieldName', 'default');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Collection Structure does not contain field fieldName
     */
    public function testSetValueWithException()
    {
        $this->recordCollection->setValue('fieldName', 'default');
    }
}
