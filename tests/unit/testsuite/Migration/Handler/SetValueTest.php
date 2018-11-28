<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class SetValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $value = 'value';
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getFields']
        );
        $record->expects($this->once())->method('setValue')->with($fieldName, $value);
        $record->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));

        $record2 = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new SetValue($value);
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }

    /**
     * @return void
     */
    public function testHandleException()
    {
        $value = 'value';
        $record = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['getFields']
        );
        $record->expects($this->once())->method('getFields')->will($this->returnValue([]));
        $handler = new SetValue($value);
        $record2 = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->expectException('Exception');
        $handler->handle($record, $record2);
    }
}
