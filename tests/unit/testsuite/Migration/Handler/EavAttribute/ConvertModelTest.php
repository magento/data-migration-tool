<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttribute;

use Migration\ResourceModel\Record;

/**
 * Class ConvertModelTest
 */
class ConvertModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConvertModel
     */
    protected $handler;

    /**
     * @var string
     */
    protected $fieldName;

    /**
     * @var \Migration\Reader\ClassMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMap;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->classMap = $this->getMockBuilder(\Migration\Reader\ClassMap::class)->setMethods(['convertClassName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new ConvertModel($this->classMap);
        $this->handler->setField($this->fieldName);
    }

    /**
     * @return void
     */
    public function testHandleConvert()
    {
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $oppositeRecord */
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMap->expects($this->once())->method('convertClassName')
            ->with('some\class_name')
            ->will($this->returnValue('Some\Class\Name'));

        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$this->fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($this->fieldName)
            ->will($this->returnValue('some\class_name'));
        $recordToHandle->expects($this->once())->method('setValue')->with($this->fieldName, 'Some\Class\Name');

        $this->handler->handle($recordToHandle, $oppositeRecord);
    }

    /**
     * @return void
     */
    public function testHandleGetDestination()
    {
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $oppositeRecord */
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)->disableOriginalConstructor()
            ->setMethods(['getValue'])
            ->getMock();
        $oppositeRecord->expects($this->once())->method('getValue')->will($this->returnValue('Some\Class\Name'));

        $this->classMap->expects($this->once())->method('convertClassName');

        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$this->fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($this->fieldName)
            ->will($this->returnValue(null));
        $recordToHandle->expects($this->once())->method('setValue')->with($this->fieldName, 'Some\Class\Name');

        $this->handler->handle($recordToHandle, $oppositeRecord);
    }
}
