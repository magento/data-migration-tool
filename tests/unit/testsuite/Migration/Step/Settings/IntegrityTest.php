<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Settings;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
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
        $this->recordFactory = $this->createPartialMock(
            \Migration\ResourceModel\RecordFactory::class,
            ['create']
        );
        $this->handlerManagerFactory = $this->createPartialMock(
            \Migration\Handler\ManagerFactory::class,
            ['create']
        );
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['error', 'notice']
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
        $this->logger
            ->expects($this->once())
            ->method('notice')
            ->with(
                'Please check if table names uses prefix, add it to your config.xml file'
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
        $this->logger
            ->expects($this->once())
            ->method('notice')
            ->with(
                'Please check if table names uses prefix, add it to your config.xml file'
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
