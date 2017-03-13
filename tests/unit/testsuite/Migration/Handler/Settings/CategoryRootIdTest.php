<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

class CategoryRootIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $categoryRootId = 2;
        $categoryRootIdHandled = null;
        $fieldName = 'value';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMock(
            'Migration\ResourceModel\Record',
            ['getValue', 'setValue', 'getFields'],
            [],
            '',
            false
        );
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->willReturn($categoryRootId);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $categoryRootIdHandled);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();
        $handler = new \Migration\Handler\Settings\CategoryRootId();
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
