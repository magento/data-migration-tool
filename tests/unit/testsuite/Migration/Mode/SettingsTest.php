<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Mode;

class SettingsTest extends \PHPUnit_Framework_TestCase
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
     * @return void
     */
    public function setUp()
    {
        $this->stepList = $this->getMockBuilder('\Migration\App\Mode\StepList')->disableOriginalConstructor()
            ->setMethods(['getSteps'])
            ->getMock();
        /** @var \Migration\App\Mode\StepListFactory|\PHPUnit_Framework_MockObject_MockObject $stepListFactory */
        $stepListFactory = $this->getMockBuilder('\Migration\App\Mode\StepListFactory')->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $stepListFactory->expects($this->any())->method('create')->with(['mode' => 'settings'])
            ->willReturn($this->stepList);
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['info', 'warning'])
            ->getMock();
        $this->progress = $this->getMockBuilder('\Migration\App\Progress')->disableOriginalConstructor()
            ->setMethods(['saveResult', 'isCompleted'])
            ->getMock();

        $this->settings = new Settings($this->progress, $this->logger, $stepListFactory);
    }

    /**
     * @return void
     */
    public function testRunStepsIntegrityFail()
    {
        $this->setExpectedException('Migration\Exception', 'Integrity Check failed');
        $step = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
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
        $stepIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
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
        $this->setExpectedException('Migration\Exception', 'Data Migration failed');
        $stepIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(false));

        $stepVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
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
        $stepIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
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
        $stepIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepIntegrity->expects($this->never())->method('perform');

        $stepData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepData->expects($this->never())->method('perform');

        $stepVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
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
