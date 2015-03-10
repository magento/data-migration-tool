<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttribute;

use Migration\Resource\Record;

/**
 * Class ConvertModelTest
 */
class ConvertModelTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleConvert()
    {
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\Resource\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $oppositeRecord */
        $oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();

        $classMap = $this->getMockBuilder('\Migration\ClassMap')->disableOriginalConstructor()
            ->setMethods(['convertClassName'])
            ->getMock();
        $classMap->expects($this->once())->method('convertClassName')
            ->with('some\class_name')
            ->will($this->returnValue('Some\Class\Name'));

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)
            ->will($this->returnValue('some\class_name'));
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 'Some\Class\Name');

        $handler = new ConvertModel($classMap);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }

    public function testHandleGetDestination()
    {
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\Resource\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $oppositeRecord */
        $oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $oppositeRecord->expects($this->exactly(2))->method('getValue')->will($this->returnValue('Some\Class\Name'));

        $classMap = $this->getMockBuilder('\Migration\ClassMap')->disableOriginalConstructor()->getMock();
        $classMap->expects($this->never())->method('convertClassName');

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->will($this->returnValue(null));
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 'Some\Class\Name');

        $handler = new ConvertModel($classMap);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
