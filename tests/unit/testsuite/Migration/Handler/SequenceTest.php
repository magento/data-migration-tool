<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler;

class SequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $table
     * @param int $id
     *
     * @return void
     * @dataProvider dataProviderTables
     */
    public function testHandle($table, $id)
    {
        $fieldName = 'fieldname';
        $createdVersionField = 'fieldname_version';
        $version = 1;

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();

        $record->expects($this->any())->method('getFields')->willReturn([$fieldName]);
        $record->expects($this->any())->method('getValue')->with($fieldName)->willReturn($id);
        $record->expects($this->any())->method('setValue')->with($fieldName, $id);

        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record2 */
        $record2 = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->setMethods(['setValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $record2->expects($this->any())->method('setValue')->willReturn($createdVersionField, $version);

        /** @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject $destination */
        $destination = $this->getMockBuilder('Migration\ResourceModel\Destination')
            ->setMethods(['clearDocument', 'saveRecords'])
            ->disableOriginalConstructor()
            ->getMock();

        $destination->expects($this->any())->method('clearDocument')->with($table);
        $destination->expects($this->any())->method('saveRecords')->with($table, [['sequence_value' => $id]])
            ->willReturnSelf();

        $handler = new Sequence($table, $destination);
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }

    /**
     * @return array
     */
    public function dataProviderTables()
    {
        return [
            ['table' => 'tablename', 'id' => 1],
            ['table' => '',          'id' => 1],
            ['table' => 'tablename', 'id' => 0],
            ['table' => '',          'id' => 0],
        ];
    }
}
