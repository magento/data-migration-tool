<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Settings;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
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
     * @var Integrity
     */
    protected $integrity;

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
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getRecordsCount', 'getRecords', 'getDocument', 'getDocumentList', 'clearDocument', 'saveRecords'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\ResourceModel\Source',
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
        $this->recordFactory = $this->getMock('Migration\ResourceModel\RecordFactory', ['create'], [], '', false);
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

    /**
     * @return void
     */
    public function testPerform()
    {
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->source->expects($this->once())->method('getDocumentList')->willReturn(['core_config_data']);
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['core_config_data']);
        $this->integrity = new Integrity(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory
        );
        $this->assertTrue($this->integrity->perform());
    }

    /**
     * @return void
     */
    public function testPerformSourceFail()
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
        $this->integrity = new Integrity(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory
        );
        $this->assertFalse($this->integrity->perform());
    }

    /**
     * @return void
     */
    public function testPerformDestinationFail()
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
        $this->integrity = new Integrity(
            $this->destination,
            $this->source,
            $this->logger,
            $this->progress,
            $this->recordFactory,
            $this->readerSettings,
            $this->handlerManagerFactory
        );
        $this->assertFalse($this->integrity->perform());
    }
}
