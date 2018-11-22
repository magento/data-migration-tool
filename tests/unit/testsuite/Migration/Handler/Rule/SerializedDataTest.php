<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\ResourceModel\Record;

/**
 * Class SerializedDataTest
 */
class SerializedDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $data = serialize([
            'key1' => 'some\class_name_1',
            'key2' => ['some\class_name_2']
        ]);
        $convertedData = serialize([
            'key1' => 'Some\Class\Name1',
            'key2' => ['Some\Class\Name2']
        ]);

        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $oppositeRecord */
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        $classMap = $this->getMockBuilder(\Migration\Reader\ClassMap::class)->disableOriginalConstructor()
            ->setMethods(['convertClassName'])
            ->getMock();
        $classMap->expects($this->exactly(2))->method('convertClassName')->will($this->returnValueMap([
            ['some\class_name_1', 'Some\Class\Name1'],
            ['some\class_name_2', 'Some\Class\Name2']
        ]));

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)->will($this->returnValue($data));
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $convertedData);

        $handler = new SerializedData($classMap);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
