<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Mode;

class SettingsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var \Migration\App\Mode\StepList|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stepList;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\App\Progress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configReader;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->stepList = $this->getMockBuilder(\Migration\App\Mode\StepList::class)->disableOriginalConstructor()
            ->setMethods(['getSteps'])
            ->getMock();
        /** @var \Migration\App\Mode\StepListFactory|\PHPUnit_Framework_MockObject_MockObject $stepListFactory */
        $stepListFactory = $this->getMockBuilder(\Migration\App\Mode\StepListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $stepListFactory->expects($this->any())->method('create')->with(['mode' => 'settings'])
            ->willReturn($this->stepList);
        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)->disableOriginalConstructor()
            ->setMethods(['info', 'warning', 'notice'])
            ->getMock();
        $this->progress = $this->getMockBuilder(\Migration\App\Progress::class)->disableOriginalConstructor()
            ->setMethods(['saveResult', 'isCompleted'])
            ->getMock();
        $this->configReader = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()
            ->getMock();

        $this->settings = new Settings($this->progress, $this->logger, $stepListFactory, $this->configReader);
    }

    /**
     * @return void
     */
    public function testRunStepsIntegrityFail()
    {
        $this->expectException(\Migration\Exception::class);
        $this->expectExceptionMessage('Integrity Check failed');
        $step = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $step->expects($this->once())->method('perform')->will($this->returnValue(false));
        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->stepList->expects($this->once())->method('getSteps')
            ->willReturn(['Title' => ['integrity' => $step]]);
        $this->assertSame($this->settings, $this->settings->run());
    }

    /**
     * @return void
     */
    public function testRunStepsVolumeFail()
    {
        $this->logger->expects($this->once())->method('warning')->with('Volume Check failed');
        $stepIntegrity = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepData = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepVolume = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepVolume->expects($this->once())->method('perform')->will($this->returnValue(false));

        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->logger->expects($this->any())->method('info');
        $this->stepList->expects($this->any())->method('getSteps')
            ->willReturn(['Title' => ['integrity' => $stepIntegrity, 'data' => $stepData, 'volume' => $stepVolume]]);
        $this->assertTrue($this->settings->run());
    }

    /**
     * @return void
     */
    public function testRunStepsDataMigrationFail()
    {
        $this->expectException(\Migration\Exception::class);
        $this->expectExceptionMessage('Data Migration failed');
        $stepIntegrity = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepData = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(false));

        $stepVolume = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepVolume->expects($this->never())->method('perform');

        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->logger->expects($this->any())->method('info');
        $this->stepList->expects($this->any())->method('getSteps')
            ->willReturn(['Title' => ['integrity' => $stepIntegrity, 'data' => $stepData, 'volume' => $stepVolume]]);
        $this->assertSame($this->settings, $this->settings->run());
    }

    /**
     * @return void
     */
    public function testRunStepsSuccess()
    {
        $stepIntegrity = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepData = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepVolume = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepVolume->expects($this->once())->method('perform')->will($this->returnValue(true));

        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->logger->expects($this->at(0))->method('info')->with("started");
        $this->logger->expects($this->at(1))->method('info')->with("started");
        $this->logger->expects($this->at(2))->method('info')->with("started");
        $this->logger->expects($this->at(3))->method('info')->with("Migration completed");
        $this->stepList->expects($this->any())->method('getSteps')
            ->willReturn(['Title' => ['integrity' => $stepIntegrity, 'data' => $stepData, 'volume' => $stepVolume]]);
        $this->assertTrue($this->settings->run());
    }

    /**
     * @return void
     */
    public function testRunStepsWithSuccessProgress()
    {
        $stepIntegrity = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepIntegrity->expects($this->never())->method('perform');

        $stepData = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepData->expects($this->never())->method('perform');

        $stepVolume = $this->getMockBuilder(\Migration\App\Step\StageInterface::class)->getMock();
        $stepVolume->expects($this->never())->method('perform');

        $this->progress->expects($this->never())->method('saveResult');
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(true);
        $this->logger->expects($this->at(0))->method('info')->with("started");
        $this->logger->expects($this->at(1))->method('info')->with("started");
        $this->logger->expects($this->at(2))->method('info')->with("started");
        $this->logger->expects($this->at(3))->method('info')->with("Migration completed");
        $this->stepList->expects($this->any())->method('getSteps')
            ->willReturn(['Title' => ['integrity' => $stepIntegrity, 'data' => $stepData, 'volume' => $stepVolume]]);
        $this->assertTrue($this->settings->run());
    }
}
