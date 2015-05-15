<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

class CategoryRootIdTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $categoryRootId = 2;
        $categoryRootIdHandled = null;
        $fieldName = 'value';
        /** @var \Migration\Resource\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMock(
            'Migration\Resource\Record',
            ['getValue', 'setValue', 'getFields'],
            [],
            '',
            false
        );
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->willReturn($categoryRootId);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $categoryRootIdHandled);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();
        $handler = new \Migration\Handler\Settings\CategoryRootId();
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
