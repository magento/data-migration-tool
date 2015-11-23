<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\ResourceModel;

/**
 * Class VolumeTest
 */
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
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

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
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('log_map_file')->willReturn($this->map);

        $this->readerGroups = $this->getMock('\Migration\Reader\Groups', ['getGroup'], [], '', false);
        $this->readerGroups->expects($this->any())->method('getGroup')->willReturnMap(
            [
                ['source_documents', ['document1' => '']],
                ['destination_documents_to_clear', ['document_to_clear' => '']]
            ]
        );

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMock('\Migration\Reader\GroupsFactory', ['create'], [], '', false);
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('log_document_groups_file')
            ->willReturn($this->readerGroups);

        $this->volume = new Volume(
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory,
            $this->progress,
            $groupsFactory
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', true, 3], ['document_to_clear', true, null]]);
        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformIgnored()
    {
        $dstDocName = false;
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->never())->method('getRecordsCount');
        $this->destination->expects($this->once())->method('getRecordsCount')->willReturn(null);
        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformFailed()
    {
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(2);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', 3], ['document_to_clear', null]]);
        $this->logger->expects($this->once())->method('addRecord')->with(
            Logger::WARNING,
            'Mismatch of entities in the document: ' . $dstDocName
        );
        $this->assertFalse($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformCheckLogsClearFailed()
    {
        $dstDocName = 'config_data';
        $this->map->expects($this->once())->method('getDocumentMap')->willReturn($dstDocName);
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);
        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['config_data', true, 3], ['document_to_clear', true, 1]]);
        $this->logger->expects($this->once())->method('addRecord')->with(
            Logger::WARNING,
            'Log documents in the destination resource are not cleared'
        );
        $this->assertFalse($this->volume->perform());
    }
}
