<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class SetValueTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $value = 'value';
        $fieldName = 'fieldname';
        /** @var \Migration\Resource\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock('Migration\Resource\Record', ['setValue', 'getFields'], [], '', false);
        $record->expects($this->once())->method('setValue')->with($fieldName, $value);
        $record->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));

        $record2 = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();

        $handler = new SetValue($value);
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }

    public function testHandleException()
    {
        $value = 'value';
        $record = $this->getMock('Migration\Resource\Record', ['getFields'], [], '', false);
        $record->expects($this->once())->method('getFields')->will($this->returnValue([]));
        $handler = new SetValue($value);
        $record2 = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();
        $this->setExpectedException('Exception');
        $handler->handle($record, $record2);
    }
}
