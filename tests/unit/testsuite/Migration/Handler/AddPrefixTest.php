<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

/**
 * Class AddPrefixTest
 */
class AddPrefixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $prefix = 'prefix';
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->will($this->returnValue('val'));
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $prefix . 'val');

        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new AddPrefix($prefix);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
