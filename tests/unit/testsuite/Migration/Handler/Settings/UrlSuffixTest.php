<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

class UrlSuffixTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $urlSuffix = 'html';
        $urlSuffixHandled = '.html';
        $fieldName = 'value';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['getValue', 'setValue', 'getFields']
        );
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->willReturn($urlSuffix);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $urlSuffixHandled);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        $handler = new \Migration\Handler\Settings\UrlSuffix();
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
