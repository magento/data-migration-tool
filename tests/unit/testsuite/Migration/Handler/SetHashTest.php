<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class SetHashTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $hash           = 'crc32';
        $baseField      = 'baseField';
        $baseFieldValue = 'some_string';
        $fieldName      = 'fieldname';

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getFields']
        );
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $hash($baseFieldValue));
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));

        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $oppositeRecord->expects($this->any())
            ->method('getValue')
            ->with($baseField)
            ->will($this->returnValue($baseFieldValue));

        $handler = new SetHash($hash, $baseField);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
