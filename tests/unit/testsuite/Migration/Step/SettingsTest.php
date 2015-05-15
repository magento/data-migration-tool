<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

class SettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
    {
        $this->destination = $this->getMock(
            'Migration\Resource\Destination',
            ['getRecordsCount', 'getRecords', 'getDocument', 'getDocumentList', 'clearDocument', 'saveRecords'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\Resource\Source',
            ['getRecordsCount', 'getRecords', 'getDocumentList'],
            [],
            '',
            false
        );
        $this->readerSettings = $this->getMock(
            'Migration\Reader\Settings',
            ['isNodeIgnored', 'getNodeMap', 'getValueHandler'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('Migration\Resource\RecordFactory', ['create'], [], '', false);
        $this->handlerManagerFactory = $this->getMock('Migration\Handler\ManagerFactory', ['create'], [], '', false);
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->progress = $this->getMock(
            'Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'advance', 'finish'],
            [],
            '',
            false
        );
    }


    public function testIntegrity()
    {
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->source->expects($this->once())->method('getDocumentList')->willReturn(['core_config_data']);
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['core_config_data']);
        $this->settings = new Settings(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory,
            'integrity'
        );
        $this->assertTrue($this->settings->perform());
    }

    public function testIntegritySourceFail()
    {
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->never())->method('finish');
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([]);
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Integrity check failed due to "core_config_data" document does not exist in the source resource'
            );
        $this->settings = new Settings(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory,
            'integrity'
        );
        $this->assertFalse($this->settings->perform());
    }

    public function testIntegrityDestinationFail()
    {
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->never())->method('finish');
        $this->source->expects($this->once())->method('getDocumentList')->willReturn(['core_config_data']);
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn([]);
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                'Integrity check failed due to "core_config_data" document does not exist in the destination resource'
            );
        $this->settings = new Settings(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory,
            'integrity'
        );
        $this->assertFalse($this->settings->perform());
    }

    public function testData()
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
        $document = $this->getMock('Migration\Resource\Document', [], [], '', false);
        $destinationRecord = $this->getMock('Migration\Resource\Record', [], [], '', false);
        $sourceRecord = $this->getMock('Migration\Resource\Record', ['getData', 'getValue', 'setValue'], [], '', false);
        $sourceRecord->expects($this->any())
            ->method('getValue')
            ->with('value')
            ->willReturn($destinationRecords[0]['value']);
        $sourceRecord->expects($this->any())->method('setValue')->with('path', $pathMapped[1][0]);
        $sourceRecord->expects($this->any())->method('getData')->willReturn($sourceRecords[1]);
        $handler = $this->getMockBuilder('\Migration\Handler\HandlerInterface')->getMock();
        $handler->expects($this->any())->method('handle')->with($sourceRecord, $destinationRecord);
        $handlerManager = $this->getMock('Migration\Handler\Manager', ['initHandler', 'getHandler'], [], '', false);
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
        $this->settings = new Settings(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory,
            'data'
        );
        $this->assertTrue($this->settings->perform());
    }
}
