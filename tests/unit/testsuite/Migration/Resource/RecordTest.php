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
        $document = $this->getMock('\Migration\Resource\Document', ['getStructure'], [], '', false);
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($this->structure));
        $this->record = new \Migration\Resource\Record(['id' => 10, 'name' => 'item1'], $document);
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
        $this->record->setStructure(null);
        $this->assertEquals($result, $this->record->validateStructure($structure));
    }

    public function validateStructureDataProvider()
    {
        $structureValid = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structureValid->expects($this->any())->method('getFields')->willReturn(['id' => [], 'name' => []]);
        $structureValid->expects($this->any())->method('hasField')->willReturnCallback(function ($fieldName) {
            return in_array($fieldName, ['id', 'name']);
        });
        $structureValid2 = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structureValid2->expects($this->any())->method('getFields')
            ->willReturn(['id' => [], 'name' => [], 'address' => []]);
        $structureValid2->expects($this->any())->method('hasField')->willReturn(false);

        $structureNotValid = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structureNotValid->expects($this->any())->method('getFields')
            ->willReturn(['id' => []]);
        $structureNotValid->expects($this->any())->method('hasField')->willReturn(false);
        return [
            [false, null],
            [true, $structureValid],
            [true, $structureValid2],
            [false, $structureNotValid],
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
     * @expectedException \Migration\Exception
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
     * @expectedException \Migration\Exception
     * @expectedExceptionMessage Record structure does not match provided Data
     */
    public function testSetDataWithException()
    {
        $this->structure->expects($this->any())->method('getFields')->willReturn(['id', 'name']);
        $this->record->setStructure($this->structure);
        $this->record->setData(['id' => 11, 'wrongName' => 'item2']);
    }

    public function testGetData()
    {
        $this->assertEquals(['id' => 10, 'name' => 'item1'], $this->record->getData());
    }

    public function getFieldsDataProvider()
    {
        return [
            ['structureData' => ['id' => '123', 'name' => 'smnm'], 'fields' => ['id', 'name']]
        ];
    }

    /**
     * @param array $structureData
     * @param array $fields
     * @dataProvider getFieldsDataProvider
     */
    public function testGetFields($structureData, $fields)
    {
        $structure = $this->getMock('\Migration\Resource\Structure', ['getFields'], [], '', false);
        $structure->expects($this->once())->method('getFields')->will($this->returnValue($structureData));
        $this->record->setStructure($structure);
        $this->assertEquals($fields, $this->record->getFields());
    }

    public function testGetFieldsInvalid()
    {
        $this->record->setStructure(null);
        $this->setExpectedException('Exception', 'Structure not set');
        $this->record->getFields();
    }
}
