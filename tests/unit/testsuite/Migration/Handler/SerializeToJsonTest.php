<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class SerializeToJsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $array = ['some_field' => 'value'];
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock(
            'Migration\ResourceModel\Record',
            ['setValue', 'getValue', 'getFields'],
            [],
            '',
            false
        );
        $record->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn(serialize($array));
        $record->expects($this->any())->method('setValue')->with($fieldName, json_encode($array));

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record2 */
        $record2 = $this->getMockBuilder('Migration\ResourceModel\Record')->disableOriginalConstructor()->getMock();

        $handler = new SerializeToJson();
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }

    /**
     *
     * @return void
     */
    public function testHandleWeeTax()
    {
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock(
            'Migration\ResourceModel\Record',
            ['setValue', 'getValue', 'getFields'],
            [],
            '',
            false
        );

        $record->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn("89.90");
        $record->expects($this->any())->method('setValue')->with($fieldName, json_encode("89.90"));

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record2 */
        $record2 = $this->getMockBuilder('Migration\ResourceModel\Record')->disableOriginalConstructor()->getMock();

        $handler = new SerializeToJson();
        $handler->setField($fieldName);
        $handler->handle($record,  $record2);
    }
}
