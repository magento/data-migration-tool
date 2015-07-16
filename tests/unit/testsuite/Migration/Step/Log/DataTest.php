<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Handler;
use Migration\Reader\Map;
use Migration\Resource;

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
     * @var Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
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
            'Migration\Resource\Source',
            ['getDocument', 'getDocumentList', 'getRecords', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\Resource\Destination',
            ['getDocument', 'getDocumentList', 'saveRecords', 'clearDocument'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('Migration\Resource\RecordFactory', ['create'], [], '', false);
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

    public function testPerform()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->will($this->returnValue($dstDocName));

        $sourceDocument = $this->getMock('\Migration\Resource\Document', ['getRecords'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->will($this->returnValue($sourceDocument));
        $destinationDocument = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $this->destination->expects($this->once())->method('getDocument')->will(
            $this->returnValue($destinationDocument)
        );
        $recordTransformer = $this->getMock(
            'Migration\RecordTransformer',
            ['init', 'transform'],
            [],
            '',
            false
        );
        $this->recordTransformerFactory->expects($this->once())->method('create')->will(
            $this->returnValue($recordTransformer)
        );
        $recordTransformer->expects($this->once())->method('init');
        $bulk = [['id' => 4, 'name' => 'john']];
        $this->source->expects($this->at(3))->method('getRecords')->will($this->returnValue($bulk));
        $this->source->expects($this->at(4))->method('getRecords')->will($this->returnValue([]));
        $this->source->expects($this->any())->method('getRecordsCount')->will($this->returnValue(2));
        $destinationRecords =  $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $destinationDocument->expects($this->once())->method('getRecords')
            ->will($this->returnValue($destinationRecords));
        $srcRecord = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $dstRecord = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordFactory->expects($this->at(0))->method('create')->will($this->returnValue($srcRecord));
        $this->recordFactory->expects($this->at(1))->method('create')->will($this->returnValue($dstRecord));
        $recordTransformer->expects($this->once())->method('transform')->with($srcRecord, $dstRecord);
        $this->destination->expects($this->once())->method('saveRecords')->with($dstDocName, $destinationRecords);
        $this->destination->expects($this->exactly(2))->method('clearDocument');
        $this->logger->expects($this->any())->method('debug')->with('migrating', ['table' => 'source_document'])
            ->willReturn(true);
        $this->data->perform();
    }

    public function testGetMapEmptyDestinationDocumentName()
    {
        $sourceDocument = $this->getMock('\Migration\Resource\Document', ['getRecords'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->will($this->returnValue($sourceDocument));

        $recordTransformer = $this->getMock('Migration\RecordTransformer', ['transform'], [], '', false);
        $recordTransformer->expects($this->never())->method('transform');
        $this->data->perform();
    }
}
