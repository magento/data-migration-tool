<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class SerializeToJsonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     * @dataProvider handleDataProvider
     */
    public function testHandle($serializedData, $unserializedData)
    {
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['setValue', 'getValue', 'getFields']
        );
        $record->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn($serializedData);
        $record->expects($this->any())->method('setValue')->with($fieldName, $unserializedData);

        $record2 = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();
        $documentIdField = $this->getMockBuilder(\Migration\Model\DocumentIdField::class)
            ->setMethods(['getFiled'])
            ->disableOriginalConstructor()
            ->getMock();
        $logger = $this->getMockBuilder(\Migration\Logger\Logger::class)
            ->setMethods(['warning'])
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new SerializeToJson($logger, $documentIdField, true, false);
        $handler->setField($fieldName);
        $this->assertNull($handler->handle($record, $record2));
    }

    /**
     * @return array
     */
    public function handleDataProvider()
    {
        $array = ['some_field' => 'value'];
        return [
            [
                serialize($array),
                json_encode($array)
            ],
            [
                null,
                null
            ]
        ];
    }
}
