<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Class GetDestinationValueTest
 */
class GetDestinationValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandleSetNull()
    {
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getFields']
        );
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, null);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));

        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $oppositeRecord->expects($this->exactly(2))->method('getValue')
            ->with($fieldName)
            ->will($this->returnValue(null));

        $handler = new GetDestinationValue('true');
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }

    /**
     * @return void
     */
    public function testHandleSetValue()
    {
        $value = 'value';
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getFields']
        );
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $value);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));

        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $oppositeRecord->expects($this->exactly(2))->method('getValue')->with($fieldName)
            ->will($this->returnValue($value));

        $handler = new GetDestinationValue('true');
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }

    /**
     * @return void
     */
    public function testHandleKeepValueFromSource()
    {
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getFields']
        );
        $recordToHandle->expects($this->never())->method('setValue');
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));

        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $oppositeRecord->expects($this->once())->method('getValue')->with($fieldName)->will($this->returnValue(null));

        $handler = new GetDestinationValue();
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
