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
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
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
