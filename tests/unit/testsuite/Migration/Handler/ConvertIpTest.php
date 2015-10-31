<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class ConvertIpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $ip         = '127.0.0.1';
        $ipDbValue  = inet_pton($ip);

        $fieldName  = 'fieldname';

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock(
            'Migration\ResourceModel\Record',
            ['setValue', 'getValue', 'getFields'],
            [],
            '',
            false
        );
        $record->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn($ipDbValue);
        $record->expects($this->any())->method('setValue')->with($fieldName, ip2long(inet_ntop($ipDbValue)));

        $record2 = $this->getMockBuilder('Migration\ResourceModel\Record')->disableOriginalConstructor()->getMock();

        $handler = new ConvertIp();
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }
}
