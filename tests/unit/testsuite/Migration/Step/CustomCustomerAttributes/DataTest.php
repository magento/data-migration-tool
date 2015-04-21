<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\Step\CustomCustomerAttributesTest;

/**
 * Class DataTest
 */
class DataTest extends CustomCustomerAttributesTest
{
    public function testPerform()
    {
        $this->step = new Data(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->factory
        );
        $sourceAdapter = $this->getMockBuilder('\Migration\Resource\Adapter\Mysql')->disableOriginalConstructor()
            ->setMethods(['getTableDdlCopy'])
            ->getMock();
        $destAdapter = $this->getMockBuilder('\Migration\Resource\Adapter\Mysql')->disableOriginalConstructor()
            ->setMethods(['createTableByDdl', 'getTableDdlCopy'])
            ->getMock();

        $this->source->expects($this->once())->method('getAdapter')->will($this->returnValue($sourceAdapter));
        $this->destination->expects($this->once())->method('getAdapter')->will($this->returnValue($destAdapter));

        $sourceTable = $this->getMockBuilder('Magento\Framework\DB\Ddl\Table')->disableOriginalConstructor()
            ->setMethods(['getColumns'])->getMock();
        $sourceTable->expects($this->any())->method('getColumns')->will($this->returnValue([['asdf']]));

        $destinationTable = $this->getMockBuilder('Magento\Framework\DB\Ddl\Table')->disableOriginalConstructor()
            ->setMethods(['setColumn'])->getMock();
        $destinationTable->expects($this->any())->method('setColumn')->with(['asdf']);

        $destAdapter->expects($this->any())->method('getTableDdlCopy')->will($this->returnValue($destinationTable));
        $destAdapter->expects($this->any())->method('createTableByDdl')->with($destinationTable);

        $sourceAdapter->expects($this->any())->method('getTableDdlCopy')->will($this->returnValue($sourceTable));

        $destDocument = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getRecords', 'getName'])
            ->getMock();
        $destDocument->expects($this->any())->method('getName')->will($this->returnValue('some_name'));

        $recordsCollection = $this->getMockBuilder('Migration\Resource\Record\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addRecord'])
            ->getMock();
        $record = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $this->factory->expects($this->any())->method('create')->with(['document' => $destDocument])
            ->will($this->returnValue($record));
        $recordsCollection->expects($this->any())->method('addRecord')->with($record);
        $destDocument->expects($this->any())->method('getRecords')->will($this->returnValue($recordsCollection));

        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($destDocument));
        $this->source->expects($this->any())->method('getRecords')->will($this->returnValueMap(
            [
                [1, ['field_1' => 1, 'field_2' => 2]]
            ]
        ));

        $this->assertTrue($this->step->perform());
    }
    public function testRollback()
    {
        $this->step = new Data(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->factory
        );
        $this->assertTrue($this->step->rollback());
    }
}
