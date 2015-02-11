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

    /**
     * @var \Migration\Resource\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    protected function setUp()
    {
        $this->structure = $this->getMock(
            '\Migration\Resource\Structure',
            [],
            [],
            '',
            false
        );
        $this->structure->expects($this->any())->method('hasField')->willReturnCallback(function ($fieldName) {
            return in_array($fieldName, ['id', 'name']);
        });
        $this->record = new \Migration\Resource\Record(['id' => 10, 'name' => 'item1']);
    }

    /**
     * @covers \Migration\Resource\Record::getStructure
     * @covers \Migration\Resource\Record::setStructure
     */
    public function testGetStructure()
    {
        $this->record->setStructure($this->structure);
        $this->assertSame($this->structure, $this->record->getStructure());
    }

    /**
     * @dataProvider validateStructureDataProvider
     *
     * @param bool $result
     * @param \Migration\Resource\Structure|null $structure
     */
    public function testValidateStructure($result, $structure)
    {
        $this->assertEquals($result, $this->record->validateStructure($structure));
    }

    public function validateStructureDataProvider()
    {
        $structureValid = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structureValid->expects($this->any())->method('hasField')->willReturnCallback(function ($fieldName) {
            return in_array($fieldName, ['id', 'name']);
        });
        $structureNotValid = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structureNotValid->expects($this->any())->method('hasField')->willReturn(false);
        return [
            [false, null],
            [true, $structureValid],
            [false, $structureNotValid]
        ];
    }

    public function testGetValue()
    {
        $this->assertEquals('10', $this->record->getValue('id'));
    }

    public function testSetValue()
    {
        $this->record->setStructure($this->structure);
        $this->assertEquals('item1', $this->record->getValue('name'));
        $this->record->setValue('name', 'itemNew');
        $this->assertEquals('itemNew', $this->record->getValue('name'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Record structure does not contain field wrongField
     */
    public function testSetValueWithException()
    {
        $this->record->setStructure($this->structure);
        $this->record->setValue('wrongField', 'itemNew');
    }

    public function testSetData()
    {
        $this->assertEquals('item1', $this->record->getValue('name'));
        $this->record->setData(['id' => 11, 'name' => 'item2']);
        $this->assertEquals('item2', $this->record->getValue('name'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Record structure does not match provided Data
     */
    public function testSetDataWithException()
    {
        $this->record->setStructure($this->structure);
        $this->record->setData(['id' => 11, 'wrongName' => 'item2']);
    }
}
