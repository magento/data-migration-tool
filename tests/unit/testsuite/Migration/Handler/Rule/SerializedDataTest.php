<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Resource\Record;

/**
 * Class SerializedDataTest
 */
class SerializedDataTest extends \PHPUnit_Framework_TestCase
{
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
        $recordToHandle = $this->getMockBuilder('Migration\Resource\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $oppositeRecord */
        $oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();
        $classMap = $this->getMockBuilder('\Migration\Reader\ClassMap')->disableOriginalConstructor()
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
