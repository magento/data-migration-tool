<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Record;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var []
     */
    protected $records;

    /**
     * @var \Migration\Resource\Record\Collection
     */
    protected $recordCollection;

    protected function setUp()
    {
        $record1 = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record1->expects($this->any())->method('getValue')->with('name')->willReturn('item1');
        $record2 = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record2->expects($this->any())->method('getValue')->with('name')->willReturn('item2');
        $record3 = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $record3->expects($this->any())->method('getValue')->with('name')->willReturn('item3');
        $this->records = [$record1, $record2, $record3];
        $this->recordCollection = new \Migration\Resource\Record\Collection($this->records);
    }

    public function testIterator()
    {
        $result = '';

        foreach ($this->recordCollection as $key => $record) {
            $result .= ' ' . $key . '=>' . $record->getValue('name');
        }

        $this->assertEquals(' 0=>item1 1=>item2 2=>item3', $result);
    }

    public function testAddRecord()
    {
        $this->assertEquals(3, count($this->recordCollection));
        $record = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordCollection->addRecord($record);
        $this->assertEquals(4, count($this->recordCollection));
    }

    public function testGetValue()
    {
        $this->assertEquals(['item1', 'item2', 'item3'], $this->recordCollection->getValue('name'));
    }

    public function testSetValue()
    {
        $this->records[0]->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('name'), $this->equalTo('default'));
        $this->records[1]->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('name'), $this->equalTo('default'));
        $this->records[2]->expects($this->any())
            ->method('setValue')
            ->with($this->equalTo('name'), $this->equalTo('default'));
        $this->recordCollection->setValue('name', 'default');
    }
}
