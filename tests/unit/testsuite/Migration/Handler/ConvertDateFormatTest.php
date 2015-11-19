<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class ConvertDateFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $fieldValue  = '<date_format>full</date_format><date_format>long</date_format><date_format>medium</date_format>'
            . '<date_format>short</date_format>';
        $convertedValue = '<date_format>0</date_format><date_format>1</date_format><date_format>2</date_format>'
            . '<date_format>3</date_format>';
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
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn($fieldValue);
        $record->expects($this->any())->method('setValue')->with($fieldName, $convertedValue);

        $record2 = $this->getMockBuilder('Migration\ResourceModel\Record')->disableOriginalConstructor()->getMock();

        $handler = new ConvertDateFormat();
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }
}
