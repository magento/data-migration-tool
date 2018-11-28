<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\ResourceModel;

class VolumeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progressBar;

    /**
     * @var \Migration\App\Progress|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Migration\Step\Map\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['addRecord']
        );
        $this->progressBar = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\Progress::class,
            ['getProcessedEntities']
        );
        $this->source = $this->createPartialMock(
            \Migration\ResourceModel\Source::class,
            ['getDocumentList', 'getRecordsCount']
        );
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getRecordsCount', 'getDocument']
        );

        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $this->helper = $this->getMockBuilder(\Migration\Step\Map\Helper::class)->disableOriginalConstructor()
            ->setMethods(['getFieldsUpdateOnDuplicate'])
            ->getMock();

        $this->volume = new Volume(
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory,
            $this->progressBar,
            $this->helper,
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
        $destinationDocument = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination
            ->expects($this->once())
            ->method('getDocument')
            ->with($dstDocName)
            ->willReturn($destinationDocument);
        $this->helper->expects($this->once())->method('getFieldsUpdateOnDuplicate')->with($dstDocName)
            ->willReturn(false);
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
        $this->destination->expects($this->never())->method('getDocument');
        $this->helper->expects($this->never())->method('getFieldsUpdateOnDuplicate');
        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformFailed()
    {
        $sourceDocName = 'core_config_data';
        $dstDocName = 'config_data';
        $destinationDocument = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->source->expects($this->once())->method('getDocumentList')->willReturn([$sourceDocName]);
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(2);
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination
            ->expects($this->once())
            ->method('getDocument')
            ->with($dstDocName)
            ->willReturn($destinationDocument);
        $this->logger->expects($this->once())->method('addRecord')->with(
            Logger::WARNING,
            'Mismatch of entities in the document: ' . $dstDocName . ' Source: 2 Destination: 3'
        );
        $this->helper->expects($this->once())->method('getFieldsUpdateOnDuplicate')->with($dstDocName)
            ->willReturn(false);
        $this->assertFalse($this->volume->perform());
    }
}
