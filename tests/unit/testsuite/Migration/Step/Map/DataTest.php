<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

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
    protected $progressBar;

    /**
     * Progress instance, saves the state of the process
     *
     * @var \Migration\App\Progress|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    public function setUp()
    {
        $this->progressBar = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\Resource\Source',
            ['getDocument', 'getDocumentList', 'getRecords', 'getRecordsCount', 'getPageSize', 'setLastLoadedRecord'],
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
            ->setMethods(['getDocumentMap', 'getHandlerConfig'])
            ->getMock();

        /** @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject $config */
        $this->config = $this->getMockBuilder('Migration\Config')->setMethods(['getOption'])
            ->disableOriginalConstructor()->getMock();
        $this->config->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['direct_document_copy', 0],
                ['bulk_size', 100]
            ]
        );

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $this->progress = $this->getMock(
            'Migration\App\Progress',
            ['getProcessedEntities', 'addProcessedEntity'],
            [],
            '',
            false
        );

        $this->logger = $this->getMockBuilder('Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['debug'])
            ->getMock();

        $this->data = new Data(
            $this->progressBar,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->recordTransformerFactory,
            $mapFactory,
            $this->progress,
            $this->logger,
            $this->config
        );
    }

    public function testGetMapEmptyDestinationDocumentName()
    {
        $sourceDocName = 'core_config_data';
        $this->progress->expects($this->once())->method('getProcessedEntities')->will($this->returnValue([]));
        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $this->data->perform();
    }

    public function testPerform()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $this->source->expects($this->any())->method('getRecordsCount')->will($this->returnValue(2));
        $dstDocName = 'config_data';
        $this->progress->expects($this->once())->method('getProcessedEntities')->will($this->returnValue([]));
        $this->map->expects($this->once())->method('getDocumentMap')->will($this->returnValue($dstDocName));
        $this->map->expects($this->any())->method('getHandlerConfig')->willReturn(['class' => 'Handler\Class']);

        $sourceDocument = $this->getMock('\Migration\Resource\Document', ['getRecords', 'getStructure'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->will(
            $this->returnValue($sourceDocument)
        );
        $destinationDocument = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getStructure', 'getRecords'])
            ->getMock();
        $this->destination->expects($this->once())->method('getDocument')->will(
            $this->returnValue($destinationDocument)
        );
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])
            ->getMock();
        $structure->expects($this->any())->method('getFields')->willReturn(['field' => []]);

        $sourceDocument->expects($this->any())->method('getStructure')->willReturn($structure);
        $destinationDocument->expects($this->any())->method('getStructure')->willReturn($structure);

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
        $this->source->expects($this->any())->method('getRecords')->willReturnOnConsecutiveCalls($bulk, []);
        $this->source->expects($this->any())->method('setLastLoadedRecord')->withConsecutive(
            [$sourceDocName, $bulk[0]],
            [$sourceDocName, []]
        );

        $this->source->expects($this->any())->method('getPageSize')->willReturn(100);
        $destinationRecords =  $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $destinationDocument->expects($this->once())->method('getRecords')->will(
            $this->returnValue($destinationRecords)
        );

        $srcRecord = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $dstRecord = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordFactory->expects($this->at(0))->method('create')->will($this->returnValue($srcRecord));
        $this->recordFactory->expects($this->at(1))->method('create')->will($this->returnValue($dstRecord));
        $recordTransformer->expects($this->once())->method('transform')->with($srcRecord, $dstRecord);

        $this->destination->expects($this->once())->method('saveRecords')->with($dstDocName, $destinationRecords);
        $this->destination->expects($this->once())->method('clearDocument')->with($dstDocName);
        $this->logger->expects($this->any())->method('debug')->with('migrating', ['table' => $sourceDocName])
            ->willReturn(true);
        $this->data->perform();
    }

    public function testPerformJustCopy()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $this->source->expects($this->any())->method('getRecordsCount')->will($this->returnValue(2));
        $dstDocName = 'config_data';
        $this->progress->expects($this->once())->method('getProcessedEntities')->will($this->returnValue([]));
        $this->map->expects($this->once())->method('getDocumentMap')->will($this->returnValue($dstDocName));
        $this->map->expects($this->any())->method('getHandlerConfig')->willReturn([]);

        $sourceDocument = $this->getMock('\Migration\Resource\Document', ['getRecords', 'getStructure'], [], '', false);
        $bulk = [['id' => 4, 'name' => 'john']];
        $this->source->expects($this->any())->method('getRecords')->willReturnOnConsecutiveCalls($bulk, []);
        $this->source->expects($this->once())->method('getDocument')->willReturn($sourceDocument);
        $this->source->expects($this->any())->method('getPageSize')->willReturn(100);
        $this->source->expects($this->any())->method('setLastLoadedRecord')->withConsecutive(
            [$sourceDocName, $bulk[0]],
            [$sourceDocName, []]
        );
        $destinationDocument = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getStructure', 'getRecords'])
            ->getMock();
        $this->destination->expects($this->once())->method('getDocument')->will(
            $this->returnValue($destinationDocument)
        );
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])
            ->getMock();
        $structure->expects($this->any())->method('getFields')->willReturn(['field' => []]);

        $sourceDocument->expects($this->any())->method('getStructure')->willReturn($structure);
        $destinationDocument->expects($this->any())->method('getStructure')->willReturn($structure);

        $destinationRecords =  $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $destinationDocument->expects($this->once())->method('getRecords')->will(
            $this->returnValue($destinationRecords)
        );

        $dstRecord = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordFactory->expects($this->at(0))->method('create')->will($this->returnValue($dstRecord));

        $this->destination->expects($this->once())->method('saveRecords')->with($dstDocName, $destinationRecords);
        $this->destination->expects($this->once())->method('clearDocument')->with($dstDocName);
        $this->data->perform();
    }
}
