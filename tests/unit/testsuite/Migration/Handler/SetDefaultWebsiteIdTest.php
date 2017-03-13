<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class SetDefaultWebsiteIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $value = '1';
        $fieldName = 'website_id';
        $records = [
            [
                'website_id' => '0',
                'is_default' => '0'
            ],
            [
                'website_id' => '1',
                'is_default' => '1'
            ]
        ];
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $recordToHandle = $this->getMock('Migration\ResourceModel\Record', ['setValue', 'getFields'], [], '', false);
        $recordOpposite = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $source = $this->getMock('Migration\ResourceModel\Source', ['getRecords'], [], '', false);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $value);
        $recordToHandle->expects($this->once())->method('getFields')->willReturn([$fieldName]);
        $source->expects($this->once())->method('getRecords')->willReturn($records);

        $handler = new SetDefaultWebsiteId($source);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $recordOpposite);
    }
}
