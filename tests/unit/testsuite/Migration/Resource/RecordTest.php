<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Record
     */
    protected $record;

    protected function setUp()
    {
        $this->record = new \Migration\Resource\Record(['id' => 10, 'name' => 'item1']);
    }

    public function testGetValue()
    {
        $this->assertEquals('10', $this->record->getValue('id'));
    }

    public function testSetValue()
    {
        $this->assertEquals('item1', $this->record->getValue('name'));
        $this->record->setValue('name', 'itemNew');
        $this->assertEquals('itemNew', $this->record->getValue('name'));
    }

    public function testSetData()
    {
        $this->assertEquals('item1', $this->record->getValue('name'));
        $this->record->setData(['id' => 11, 'name' => 'item2']);
        $this->assertEquals('item2', $this->record->getValue('name'));
    }
}
