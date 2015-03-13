<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

/**
 * Class CustomCustomerAttributesTest
 */
class CustomCustomerAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\CustomCustomerAttributes
     */
    protected $step;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getSource'])
            ->getMock();
        $this->config->expects($this->any())->method('getSource')->will(
            $this->returnValue(['type' => DatabaseStep::SOURCE_TYPE])
        );

        $this->source = $this->getMockBuilder('Migration\Resource\Source')->disableOriginalConstructor()
            ->setMethods(['getDocument', 'getRecordsCount', 'getAdapter', 'addDocumentPrefix', 'getRecords'])
            ->getMock();
        $this->destination = $this->getMockBuilder('Migration\Resource\Destination')->disableOriginalConstructor()
            ->setMethods(['getDocument', 'getRecordsCount', 'getAdapter', 'addDocumentPrefix', 'saveRecords'])
            ->getMock();
        $this->progress = $this->getMockBuilder('Migration\ProgressBar')->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->progress->expects($this->any())->method('start')->with(4);
        $this->progress->expects($this->any())->method('finish');
        $this->progress->expects($this->any())->method('advance');

        $this->factory = $this->getMockBuilder('Migration\Resource\RecordFactory')->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->step = new CustomCustomerAttributes(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->factory
        );
    }

    public function testIntegrity()
    {
        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()->getMock();

        $this->source->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));

        $this->assertTrue($this->step->integrity());
    }

    public function testRun()
    {
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

        $this->assertTrue($this->step->run());
    }

    public function testVolumeCheck()
    {
        $fields = ['field_name' => []];

        $structure = $this->getMockBuilder('Migration\Resource\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getStructure'])
            ->getMock();
        $document->expects($this->exactly(8))->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));

        $this->source->expects($this->exactly(4))->method('getRecordsCount')->with()->will($this->returnValue(1));
        $this->destination->expects($this->exactly(4))->method('getRecordsCount')->with()->will($this->returnValue(1));

        $this->assertTrue($this->step->volumeCheck());
    }

    public function testGetTitle()
    {
        $this->assertEquals('Custom Customer Attributes Step', $this->step->getTitle());
    }

    public function testRollback()
    {
        $this->assertTrue($this->step->rollback());
    }
}
