<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\Reader\MapInterface;
use Migration\Step\DatabaseStage;
use Migration\Reader\Map;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\Step\CustomCustomerAttributes\Data
     */
    protected $step;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groups;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()
            ->setMethods(['getSource'])
            ->getMock();
        $this->config->expects($this->any())->method('getSource')->will(
            $this->returnValue(['type' => DatabaseStage::SOURCE_TYPE])
        );

        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)->disableOriginalConstructor()
            ->setMethods(['getDocument', 'getRecordsCount', 'getAdapter', 'addDocumentPrefix', 'getRecords'])
            ->getMock();
        $this->source->expects($this->any())->method('addDocumentPrefix')->willReturnCallback(function ($name) {
            return 'source_suffix_' . $name;
        });
        $this->destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument', 'getRecordsCount', 'getAdapter', 'addDocumentPrefix', 'saveRecords'])
            ->getMock();
        $this->destination->expects($this->any())->method('addDocumentPrefix')->willReturnCallback(function ($name) {
            return 'destination_suffix_' . $name;
        });
        $this->progress = $this->getMockBuilder(\Migration\App\ProgressBar\LogLevelProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->progress->expects($this->any())->method('start');
        $this->progress->expects($this->any())->method('finish');
        $this->progress->expects($this->any())->method('advance');

        $this->recordFactory = $this->getMockBuilder(\Migration\ResourceModel\RecordFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'init', 'getDocumentList', 'getDestDocumentsToClear'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMockBuilder(\Migration\Reader\MapFactory::class)->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $mapFactory->expects($this->any())->method('create')->with('customer_attr_map_file')->willReturn($this->map);

        $this->groups = $this->getMockBuilder(\Migration\Reader\Groups::class)->disableOriginalConstructor()
            ->getMock();

        $this->groups->expects($this->any())->method('getGroup')->with('source_documents')->willReturn([
                'source_document_1' => 'entity_id',
        ]);

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMockBuilder(\Migration\Reader\GroupsFactory::class)->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $groupsFactory->expects($this->any())->method('create')->with('customer_attr_document_groups_file')
            ->willReturn($this->groups);

        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();
        $this->step = new Data(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress,
            $this->recordFactory,
            $mapFactory,
            $groupsFactory,
            $this->logger
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $this->map->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['source_document_1', MapInterface::TYPE_SOURCE, 'destination_document_1'],
            ]
        ) ;
        $sourceTable = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)->disableOriginalConstructor()
            ->setMethods(['getColumns'])->getMock();
        $sourceTable->expects($this->any())->method('getColumns')->will($this->returnValue([['asdf']]));

        $sourceAdapter = $this->getMockBuilder(\Migration\ResourceModel\Adapter\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTableDdlCopy'])
            ->getMock();
        $sourceAdapter->expects($this->any())->method('getTableDdlCopy')
            ->with('source_suffix_source_document_1', 'destination_suffix_destination_document_1')
            ->will($this->returnValue($sourceTable));

        $destinationTable = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)->disableOriginalConstructor()
            ->setMethods(['setColumn'])->getMock();
        $destinationTable->expects($this->any())->method('setColumn')->with(['asdf']);
        $destAdapter = $this->getMockBuilder(\Migration\ResourceModel\Adapter\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods(['createTableByDdl', 'getTableDdlCopy'])
            ->getMock();
        $destAdapter->expects($this->any())->method('getTableDdlCopy')
            ->with('destination_suffix_destination_document_1', 'destination_suffix_destination_document_1')
            ->will($this->returnValue($destinationTable));
        $destAdapter->expects($this->any())->method('createTableByDdl')->with($destinationTable);

        $this->source->expects($this->once())->method('getAdapter')->will($this->returnValue($sourceAdapter));
        $this->destination->expects($this->once())->method('getAdapter')->will($this->returnValue($destAdapter));

        $recordsCollection = $this->getMockBuilder(\Migration\ResourceModel\Record\Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['addRecord'])
            ->getMock();

        $destDocument = $this->getMockBuilder(\Migration\ResourceModel\Document::class)->disableOriginalConstructor()
            ->setMethods(['getRecords', 'getName'])
            ->getMock();
        $destDocument->expects($this->any())->method('getName')->will($this->returnValue('some_name'));
        $destDocument->expects($this->any())->method('getRecords')->will($this->returnValue($recordsCollection));

        $record = $this->getMockBuilder(\Migration\ResourceModel\Record::class)->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();
        $record->expects($this->once())->method('setData')->with(['field_1' => 1, 'field_2' => 2]);
        $this->recordFactory->expects($this->any())->method('create')->with(['document' => $destDocument])
            ->will($this->returnValue($record));
        $recordsCollection->expects($this->any())->method('addRecord')->with($record);

        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($destDocument));
        $this->logger->expects($this->any())->method('debug')->with('migrating', ['table' => 'source_document_1'])
            ->willReturn(true);
        $this->source->expects($this->any())->method('getRecords')->will($this->returnValueMap(
            [
                ['source_document_1', 0, null, [['field_1' => 1, 'field_2' => 2]]]
            ]
        ));

        $this->assertTrue($this->step->perform());
    }
}
