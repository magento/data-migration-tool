<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Handler;
use Migration\Reader\Map;
use Migration\ResourceModel;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @var \Migration\RecordTransformerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordTransformerFactory;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $data;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var Mysql
     */
    protected $sourceAdapter;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\ResourceModel\Source',
            [
                'getDocument', 'getDocumentList', 'getRecords', 'getRecordsCount',
                'getAdapter', 'addDocumentPrefix', 'getPageSize'
            ],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getDocument', 'getDocumentList', 'saveRecords', 'clearDocument'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('Migration\ResourceModel\RecordFactory', ['create'], [], '', false);
        $this->recordTransformerFactory = $this->getMock(
            'Migration\RecordTransformerFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->map = $this->getMockBuilder('Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'init', 'getDocumentList', 'getDestDocumentsToClear'])
            ->getMock();

        $this->sourceAdapter = $this->getMockBuilder('Migration\ResourceModel\Adapter\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['getSelect', 'loadDataFromSelect'])
            ->getMock();

        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['from', 'joinLeft', 'where', 'group'])
            ->disableOriginalConstructor()
            ->getMock();

        $select->expects($this->any())->method('from')->willReturnSelf();
        $select->expects($this->any())->method('joinLeft')->willReturnSelf();
        $select->expects($this->any())->method('where')->willReturnSelf();
        $select->expects($this->any())->method('group')->willReturnSelf();

        $this->sourceAdapter->expects($this->any())->method('getSelect')->willReturn($select);

        $this->source->expects($this->any())->method('getAdapter')->willReturn($this->sourceAdapter);
        $this->source->expects($this->any())->method('addDocumentPrefix')->willReturn($this->returnArgument(1));
        $this->source->expects($this->any())->method('getPageSize')->willReturn(100);
        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('log_map_file')->willReturn($this->map);

        $this->readerGroups = $this->getMock('\Migration\Reader\Groups', ['getGroup'], [], '', false);
        $this->readerGroups->expects($this->any())->method('getGroup')->willReturnMap(
            [
                ['source_documents', ['source_document' => '']],
                ['destination_documents_to_clear', ['source_document_to_clear' => '']]
            ]
        );

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMock('\Migration\Reader\GroupsFactory', ['create'], [], '', false);
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('log_document_groups_file')
            ->willReturn($this->readerGroups);
        $this->logger = $this->getMockBuilder('Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();
        $this->data = new Data(
            $this->progress,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->recordTransformerFactory,
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
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->will($this->returnValue($dstDocName));

        $sourceDocument = $this->getMock('\Migration\ResourceModel\Document', ['getRecords'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->will($this->returnValue($sourceDocument));
        $destinationDocument = $this->getMock('\Migration\ResourceModel\Document', [], [], '', false);
        $this->destination->expects($this->once())->method('getDocument')->will(
            $this->returnValue($destinationDocument)
        );

        $this->sourceAdapter
            ->expects($this->at(1))
            ->method('loadDataFromSelect')
            ->willReturn([[
                'visitor_id'     => 1,
                'session_id'     => 'dvak7ir3t9p3sicksr0t9thqc7',
                'first_visit_at' => '2015-10-25 11:22:40',
                'last_visit_at'  => '2015-10-25 11:22:40',
                'last_url_id'    => 1,
                'store_id'       => 1,
            ]]);

        $this->sourceAdapter->expects($this->at(2))->method('loadDataFromSelect')->willReturn([]);

        $destinationRecords =  $this->getMock('\Migration\ResourceModel\Record\Collection', [], [], '', false);
        $destinationDocument->expects($this->once())->method('getRecords')
            ->will($this->returnValue($destinationRecords));
        $srcRecord = $this->getMock('\Migration\ResourceModel\Record', [], [], '', false);
        $this->recordFactory->expects($this->at(0))->method('create')->will($this->returnValue($srcRecord));

        $this->destination->expects($this->once())->method('saveRecords')->with($dstDocName, $destinationRecords);
        $this->destination->expects($this->exactly(2))->method('clearDocument');
        $this->logger->expects($this->any())->method('debug')->with('migrating', ['table' => 'source_document'])
            ->willReturn(true);
        $this->data->perform();
    }

    /**
     * @return void
     */
    public function testGetMapEmptyDestinationDocumentName()
    {
        $sourceDocument = $this->getMock('\Migration\ResourceModel\Document', ['getRecords'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->will($this->returnValue($sourceDocument));

        $this->data->perform();
    }
}
