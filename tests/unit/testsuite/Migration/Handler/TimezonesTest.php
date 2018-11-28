<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class TimezoneTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $timeAndOffset array
     * @param $expected array
     * @return void
     * @dataProvider dataProviderTimeAndOffsets
     */
    public function testHandle($timeAndOffset, $expected)
    {
        list($value, $offset, $dataType)   = array_values($timeAndOffset);
        $newValue = $expected['datetime'];

        $fieldName  = 'fieldname';

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['getValue', 'setValue', 'getFields', 'getStructure']
        );

        $structure = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods(['getFields'])
            ->getMock();

        $structure->expects($this->any())->method('getFields')->willReturn([
            $fieldName => ['DATA_TYPE' => $dataType]
        ]);

        $recordToHandle->expects($this->any())->method('getValue')->willReturn($value);
        $recordToHandle->expects($this->any())->method('setValue')->with($fieldName, $newValue);
        $recordToHandle->expects($this->any())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new Timezone($offset);
        $handler->setField($fieldName);
        $this->assertNull($handler->handle($recordToHandle, $oppositeRecord));
    }

    /**
     * @return array
     */
    public function dataProviderTimeAndOffsets()
    {
        return [
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                ],
                [
                    'datetime'  => '2015-10-06 12:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '0',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                ],
                [
                    'datetime'  => '2015-10-06 12:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '+3',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                ],
                [
                    'datetime'  => '2015-10-06 15:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '+12',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                ],
                [
                    'datetime'  => '2015-10-07 00:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '-14',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                ],
                [
                    'datetime'  => '2015-10-05 22:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '2015-10-06 12:56:36',
                    'offset'    => '-14',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                ],
                [
                    'datetime'  => '2015-10-05 22:56:36',
                ],
            ],
            [
                [
                    'datetime'  => '1447683513',
                    'offset'    => '1',
                    'DATA_TYPE' => Timezone::TYPE_INT,
                ],
                [
                    'datetime'  => '1447687113',
                ],
            ],
            [
                [
                    'datetime'  => '1447679914',
                    'offset'    => '-1',
                    'DATA_TYPE' => Timezone::TYPE_INT,
                ],
                [
                    'datetime'  => '1447676314',
                ],
            ],
            [
                [
                    'datetime'  => '1447679914',
                    'offset'    => '3',
                    'DATA_TYPE' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                ],
                [
                    'datetime'  => '1447690714',
                ],
            ],
        ];
    }
}
