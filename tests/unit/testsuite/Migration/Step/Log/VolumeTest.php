<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Logger\Logger;
use Migration\MapReader\MapReaderLog;
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
     * @var MapReaderLog|\PHPUnit_Framework_MockObject_MockObject
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

        $this->mapReader = $this->getMockBuilder('Migration\MapReader\MapReaderLog')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'init', 'getDestDocumentsToClear', 'getDocumentList'])
            ->getMock();
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
        $dstDocName = 'config_data';
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', 3], ['document_to_clear', null]]);
        $this->mapReader->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document1' => 'document2']));
        $this->mapReader->expects($this->any())->method('getDestDocumentsToClear')->willReturn(['document_to_clear']);
        $this->assertTrue($this->volume->perform());
    }

    public function testPerformIgnored()
    {
        $dstDocName = false;
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->never())->method('getRecordsCount');
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(null);
        $this->mapReader->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document1' => 'document2']));
        $this->mapReader->expects($this->any())->method('getDestDocumentsToClear')->willReturn(['document_to_clear']);
        $this->assertTrue($this->volume->perform());
    }

    public function testPerformFailed()
    {
        $dstDocName = 'config_data';
        $this->mapReader->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document1' => 'document2']));
        $this->mapReader->expects($this->any())->method('getDestDocumentsToClear')->willReturn(['document_to_clear']);
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
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
        $this->mapReader->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document1' => 'document2']));
        $this->mapReader->expects($this->any())->method('getDestDocumentsToClear')->willReturn(['document_to_clear']);
        $this->mapReader->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', 3], ['document_to_clear', 1]]);
        $this->logger->expects($this->once())->method('error')->with(
            PHP_EOL . 'Destination log documents are not cleared'
        );
        $this->assertFalse($this->volume->perform());
    }
}
