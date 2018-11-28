<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Settings;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var \Migration\Reader\Settings|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerSettings;

    /**
     * @var \Migration\Handler\ManagerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerManagerFactory;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getRecordsCount', 'getRecords', 'getDocument', 'getDocumentList', 'clearDocument', 'saveRecords']
        );
        $this->source = $this->createPartialMock(
            \Migration\ResourceModel\Source::class,
            ['getRecordsCount', 'getRecords', 'getDocumentList']
        );
        $this->readerSettings = $this->createPartialMock(
            \Migration\Reader\Settings::class,
            ['isNodeIgnored', 'getNodeMap', 'getValueHandler']
        );
        $this->recordFactory = $this->getMockBuilder(\Migration\ResourceModel\RecordFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->handlerManagerFactory = $this->getMockBuilder(\Migration\Handler\ManagerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['error']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'advance', 'finish']
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $count = 2;
        $sourceRecords = [
            ['config_id' => 1, 'scope_id' => 0, 'scope' => 'default', 'path' => 'some/path1', 'value' => 'some value4'],
            ['config_id' => 2, 'scope_id' => 0, 'scope' => 'default', 'path' => 'some/path3', 'value' => 'some value3'],
        ];
        $destinationRecords = [
            ['config_id' => 1, 'scope_id' => 0, 'scope' => 'default', 'path' => 'some/path1', 'value' => 'some value1'],
            ['config_id' => 2, 'scope_id' => 0, 'scope' => 'default', 'path' => 'some/path2', 'value' => 'some value2'],
        ];
        $destinationRecordsFinal = [
            ['scope_id' => 0, 'scope' => 'default', 'path' => 'some/path1', 'value' => 'some value1'],
            ['scope_id' => 0, 'scope' => 'default', 'path' => 'some/path2', 'value' => 'some value2'],
            ['scope_id' => 0, 'scope' => 'default', 'path' => 'some/path3', 'value' => 'some value3'],
        ];
        $pathMapped = [
            ['some/path1', 'some/path1'],
            ['some/path3', 'some/path3'],
        ];
        $handlerParams = [
            ['some/path1', ['class' => 'Some\\Class', 'params' => []]],
            ['some/path3', []]
        ];
        $document = $this->createMock(\Migration\ResourceModel\Document::class);
        $destinationRecord = $this->createMock(\Migration\ResourceModel\Record::class);
        $sourceRecord = $this->createPartialMock(
            \Migration\ResourceModel\Record::class,
            ['getData', 'getValue', 'setValue']
        );
        $sourceRecord->expects($this->any())
            ->method('getValue')
            ->with('value')
            ->willReturn($destinationRecords[0]['value']);
        $sourceRecord->expects($this->any())->method('setValue')->with('path', $pathMapped[1][0]);
        $sourceRecord->expects($this->any())->method('getData')->willReturn($sourceRecords[1]);
        $handler = $this->getMockBuilder(\Migration\Handler\HandlerInterface::class)->getMock();
        $handler->expects($this->any())->method('handle')->with($sourceRecord, $destinationRecord);
        $handlerManager = $this->createPartialMock(
            \Migration\Handler\Manager::class,
            ['initHandler', 'getHandler']
        );
        $handlerManager->expects($this->once())
            ->method('initHandler')
            ->with('value', $handlerParams[0][1], 'some/path1');
        $handlerManager->expects($this->once())->method('getHandler')->willReturn($handler);
        $this->progress->expects($this->once())->method('start')->with($count);
        $this->progress->expects($this->exactly($count))->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->source->expects($this->once())->method('getRecordsCount')->with('core_config_data')->willReturn($count);
        $this->source->expects($this->once())->method('getRecords')->with('core_config_data', 0, $count)
            ->willReturn($sourceRecords);
        $this->destination->expects($this->once())->method('getRecordsCount')->with('core_config_data')
            ->willReturn($count);
        $this->destination->expects($this->once())->method('getDocument')->with('core_config_data')
            ->willReturn($document);
        $this->destination->expects($this->once())->method('clearDocument')->with('core_config_data');
        $this->destination->expects($this->once())->method('saveRecords')
            ->with('core_config_data', $destinationRecordsFinal);
        $this->destination->expects($this->once())->method('getRecords')->with('core_config_data', 0, $count)
            ->willReturn($destinationRecords);
        $this->readerSettings->expects($this->any())->method('isNodeIgnored')->willReturn(false);
        $this->readerSettings->expects($this->any())->method('getNodeMap')->willReturnMap($pathMapped);
        $this->readerSettings->expects($this->any())->method('getValueHandler')->willReturnMap($handlerParams);
        $this->recordFactory->expects($this->at(0))->method('create')
            ->with(['document' => $document, 'data' => $sourceRecords[0]])
            ->willReturn($sourceRecord);
        $this->recordFactory->expects($this->at(1))->method('create')
            ->with(['document' => $document, 'data' => $destinationRecords[0]])
            ->willReturn($destinationRecord);
        $this->recordFactory->expects($this->at(2))->method('create')
            ->with(['document' => $document, 'data' => $sourceRecords[1]])
            ->willReturn($sourceRecord);
        $this->recordFactory->expects($this->at(3))->method('create')
            ->with(['document' => $document, 'data' => []])
            ->willReturn($destinationRecord);
        $this->handlerManagerFactory->expects($this->once())->method('create')->willReturn($handlerManager);
        $this->data = new Data(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory
        );
        $this->assertTrue($this->data->perform());
    }
}
