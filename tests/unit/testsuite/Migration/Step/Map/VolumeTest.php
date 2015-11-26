<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\ResourceModel;

class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
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
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->getMock('Migration\Logger\Logger', ['addRecord'], [], '', false);
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\ResourceModel\Source',
            ['getDocumentList', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getRecordsCount'],
            [],
            '',
            false
        );

        $this->map = $this->getMockBuilder('Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $this->volume = new Volume(
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory,
            $this->progress
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformIgnored()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $dstDocName = false;
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->never())->method('getRecordsCount');
        $this->destination->expects($this->never())->method('getRecordsCount');
        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformFailed()
    {
        $sourceDocName = 'core_config_data';
        $dstDocName = 'config_data';
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(2);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->logger->expects($this->once())->method('addRecord')->with(
            Logger::WARNING,
            'Mismatch of entities in the document: ' . $dstDocName
        );
        $this->assertFalse($this->volume->perform());
    }
}
