<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

class CategoryRootIdTest extends \PHPUnit\Framework\TestCase
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
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['getValue', 'setValue', 'getFields']
        );
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->willReturn($categoryRootId);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $categoryRootIdHandled);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler = new \Migration\Handler\Settings\CategoryRootId();
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
