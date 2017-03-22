<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class SerializeToJsonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     * @dataProvider testHandleDataProvider
     */
    public function testHandle($serializedData, $unserializedData)
    {
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getValue', 'getFields'],
            [],
            '',
            false
        );
        $record->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn($serializedData);
        $record->expects($this->any())->method('setValue')->with($fieldName, $unserializedData);

        $record2 = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new SerializeToJson();
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }

    public function testHandleDataProvider()
    {
        $array = ['some_field' => 'value'];
        return [
            [
                serialize($array),
                json_encode($array)
            ],
            [
                null,
                null
            ]
        ];
    }
}
