<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class TimezoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $timeAndOffset array
     * @param $expected array
     * @return void
     * @dataProvider dataProviderTimeAndOffsets
     */
    public function testHandle($timeAndOffset, $expected)
    {
        list($value, $offset)   = array_values($timeAndOffset);
        $newValue = $expected['datetime'];

        $fieldName  = 'fieldname';

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMock(
            'Migration\ResourceModel\Record',
            ['getValue', 'setValue', 'getFields'],
            [],
            '',
            false
        );

        $recordToHandle->expects($this->any())->method('getValue')->willReturn($value);
        $recordToHandle->expects($this->any())->method('setValue')->with($fieldName, $newValue);
        $recordToHandle->expects($this->any())->method('getFields')->will($this->returnValue([$fieldName]));

        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new Timezone($offset);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }

    /**
     * @return array
     */
    public function dataProviderTimeAndOffsets()
    {
        return [
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '',
                ],
                [
                    'datetime'  => '2015-10-06 12:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '0',
                ],
                [
                    'datetime'  => '2015-10-06 12:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '+3',
                ],
                [
                    'datetime'  => '2015-10-06 15:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '+12',
                ],
                [
                    'datetime'  => '2015-10-07 00:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '-14',
                ],
                [
                    'datetime'  => '2015-10-05 22:56:36',
                ],
            ],
        ];
    }
}
