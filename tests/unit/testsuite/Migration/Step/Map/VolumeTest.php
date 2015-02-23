<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

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
     * @var MapReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    /**
     * @var Volume
     */
    protected $volume;

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
        $this->mapReader = $this->getMock('Migration\MapReader', ['getDocumentMap', 'init'], [], '', false);
        $this->mapReader->expects($this->once())->method('init');
        $this->volume = new Volume(
            $this->logger,
            $this->source,
            $this->destination,
            $this->mapReader,
            $this->progress
        );
    }

    public function testPerform()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $dstDocName = 'config_data';
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->assertTrue($this->volume->perform());
    }

    public function testPerformIgnored()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $dstDocName = false;
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->never())->method('getRecordsCount');
        $this->destination->expects($this->never())->method('getRecordsCount');
        $this->assertTrue($this->volume->perform());
    }

    public function testPerformFailed()
    {
        $sourceDocName = 'core_config_data';
        $dstDocName = 'config_data';
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(2);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->logger->expects($this->once())->method('error')->with(
            PHP_EOL . 'Volume check failed for the destination document ' .
            PHP_EOL . $dstDocName
        );
        $this->assertFalse($this->volume->perform());
    }
}
