<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\Resource;

/**
 * Class VolumeTest
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var Volume
     */
    protected $volume;

    /**
     * @var \Migration\Reader\ListsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerLists;

    public function setUp()
    {
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->progress = $this->getMock('\Migration\ProgressBar', ['start', 'finish', 'advance'], [], '', false);
        $this->source = $this->getMock(
            'Migration\Resource\Source',
            ['getDocumentList', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\Resource\Destination',
            ['getRecordsCount'],
            [],
            '',
            false
        );

        $this->map = $this->getMockBuilder('Migration\Reader\Map')->disableOriginalConstructor()
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('log_map_file')->willReturn($this->map);

        $this->readerLists = $this->getMock('\Migration\Reader\Lists', ['getList'], [], '', false);
        $this->readerLists->expects($this->any())->method('getList')->willReturnMap(
            [
                ['source_documents', ['document1']],
                ['destination_documents_to_clear', ['document_to_clear']]
            ]
        );

        /** @var \Migration\Reader\ListsFactory|\PHPUnit_Framework_MockObject_MockObject $listsFactory */
        $listsFactory = $this->getMock('\Migration\Reader\ListsFactory', ['create'], [], '', false);
        $listsFactory->expects($this->any())
            ->method('create')
            ->with('log_list_file')
            ->willReturn($this->readerLists);

        $this->volume = new Volume(
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory,
            $this->progress,
            $listsFactory
        );
    }

    public function testPerform()
    {
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', true, 3], ['document_to_clear', true, null]]);
        $this->assertTrue($this->volume->perform());
    }

    public function testPerformIgnored()
    {
        $dstDocName = false;
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->never())->method('getRecordsCount');
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(null);
        $this->assertTrue($this->volume->perform());
    }

    public function testPerformFailed()
    {
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(2);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', 3], ['document_to_clear', null]]);
        $this->logger->expects($this->once())->method('error')->with(
            PHP_EOL . 'Volume check failed for the destination document: ' . $dstDocName
        );
        $this->assertFalse($this->volume->perform());
    }

    public function testPerformCheckLogsClearFailed()
    {
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', true, 3], ['document_to_clear', true, 1]]);
        $this->logger->expects($this->once())->method('error')->with(
            PHP_EOL . 'Destination log documents are not cleared'
        );
        $this->assertFalse($this->volume->perform());
    }
}
