<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Document;

class RecordIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Record\ProviderInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordProvider;

    /**
     * @var \Migration\Resource\Record\RecordIterator
     */
    protected $recordIterator;

    protected function setUp()
    {
        $this->recordProvider = $this->getMockForAbstractClass(
            '\Migration\Resource\Record\ProviderInterface',
            array(),
            '',
            false
        );
        $this->recordIterator = new \Migration\Resource\Record\RecordIterator('test_name');
        $this->recordIterator->setRecordProvider($this->recordProvider);
    }

    public function testCurrent()
    {
        $this->recordProvider->expects($this->any())
            ->method('loadPage')
            ->with($this->equalTo('test_name'), $this->equalTo(0));
        $this->recordIterator->rewind();
        $this->recordIterator->current();
    }

    /**
     * @covers \Migration\Resource\Record\RecordIterator::key
     * @covers \Migration\Resource\Record\RecordIterator::loadPage
     */
    public function testKey()
    {
        $this->recordProvider->expects($this->any())
            ->method('getPageSize')
            ->will($this->returnValue(10));
        $this->recordProvider->expects($this->any())
            ->method('loadPage')
            ->with($this->equalTo('test_name'), $this->equalTo(0))
            ->will($this->returnValue(['item1', 'item2', 'item3', 'item4']));
        $this->recordIterator->seek(2);

        $this->assertEquals('2', $this->recordIterator->key());
    }

    public function testValid()
    {
        $this->recordProvider->expects($this->any())
            ->method('getPageSize')
            ->will($this->returnValue(10));
        $this->recordProvider->expects($this->any())
            ->method('getRecordsCount')
            ->will($this->returnValue(30));
        $this->recordProvider->expects($this->any())
            ->method('loadPage')
            ->with($this->equalTo('test_name'), $this->equalTo(2))
            ->will($this->returnValue(['item1', 'item2']));
        $this->recordIterator->setRecordProvider($this->recordProvider);
        $this->recordIterator->seek(25);
        $this->assertTrue($this->recordIterator->valid());
    }

    public function testNotValid()
    {
        $this->recordProvider->expects($this->any())
            ->method('getPageSize')
            ->will($this->returnValue(10));
        $this->recordProvider->expects($this->any())
            ->method('getRecordsCount')
            ->will($this->returnValue(10));
        $this->recordProvider->expects($this->any())
            ->method('loadPage')
            ->with($this->equalTo('test_name'), $this->equalTo(2))
            ->will($this->returnValue(['item1', 'item2']));
        $this->recordIterator->seek(25);
        $this->assertFalse($this->recordIterator->valid());
    }

    /**
     * @covers \Migration\Resource\Record\RecordIterator::count
     * @covers \Migration\Resource\Record\RecordIterator::setRecordProvider
     */
    public function testCount()
    {
        $this->recordProvider->expects($this->atLeastOnce())
            ->method('getRecordsCount')
            ->will($this->returnValue(10));
        $this->recordIterator->setRecordProvider($this->recordProvider);
        $this->assertEquals('10', $this->recordIterator->count());
    }

    public function testIterator()
    {
        $this->recordProvider->expects($this->any())
            ->method('getPageSize')
            ->will($this->returnValue(10));
        $this->recordProvider->expects($this->any())
            ->method('getRecordsCount')
            ->will($this->returnValue(5));
        $this->recordProvider->expects($this->any())
            ->method('loadPage')
            ->with($this->equalTo('test_name'), $this->equalTo(0))
            ->will($this->returnValue(['item1', 'item2', 'item3', 'item4', 'item5']));
        $this->recordIterator->setRecordProvider($this->recordProvider);

        $result = '';

        foreach($this->recordIterator as $key => $value) {
            $result .= ' ' . $key . '=>' . $value;
        }

        $this->assertEquals(' 0=>item1 1=>item2 2=>item3 3=>item4 4=>item5', $result);
    }
}
