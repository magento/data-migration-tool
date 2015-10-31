<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Mode;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    protected $data;

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
        $stepListFactory->expects($this->any())->method('create')->with(['mode' => 'data'])
            ->willReturn($this->stepList);
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['info', 'warning'])
            ->getMock();
        $this->progress = $this->getMockBuilder('\Migration\App\Progress')->disableOriginalConstructor()
            ->setMethods(['saveResult', 'isCompleted', 'reset'])
            ->getMock();
        /** @var \Migration\App\SetupDeltaLog|\PHPUnit_Framework_MockObject_MockObject $setupDeltaLog */
        $setupDeltaLog = $this->getMockBuilder('\Migration\App\SetupDeltaLog')->disableOriginalConstructor()
            ->setMethods(['perform'])
            ->getMock();
        $setupDeltaLog->expects($this->any())->method('perform')->willReturn(true);
        $this->config = $this->getMock('\Migration\Config', ['getStep'], [], '', false);

        $this->data = new Data($this->progress, $this->logger, $stepListFactory, $setupDeltaLog, $this->config);
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
            ->willReturn(['Step1' => ['integrity' => $step]]);
        $this->assertSame($this->data, $this->data->run());
    }

    /**
     * @expectedException \Migration\Exception
     * @expectedExceptionMessage Volume Check failed
     * @return void
     */
    public function testRunStepsVolumeFail()
    {
        $stepData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepData->expects($this->once())->method('perform')->will($this->returnValue(true));

        $stepVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stepVolume->expects($this->once())->method('perform')->will($this->returnValue(false));

        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->progress->expects($this->any())->method('reset')->with($stepVolume);
        $this->logger->expects($this->any())->method('info');
        $this->stepList->expects($this->any())->method('getSteps')
            ->willReturn(['Step1' => ['data' => $stepData, 'volume' => $stepVolume]]);
        $this->assertTrue($this->data->run());
    }

    /**
     * @return void
     */
    public function testRunStepsDataMigrationFail()
    {
        $this->setExpectedException('Migration\Exception', 'Data Migration failed');
        $stageIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));
        $stageData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageData->expects($this->once())->method('perform')->will($this->returnValue(false));
        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->progress->expects($this->any())->method('reset')->with($stageData);
        $this->logger->expects($this->any())->method('info');
        $this->stepList->expects($this->any())->method('getSteps')
            ->willReturn(['Step1' => ['integrity' => $stageIntegrity, 'data' => $stageData]]);
        $this->assertSame($this->data, $this->data->run());
    }

    /**
     * @return void
     */
    public function testRunStepsSuccess()
    {
        $stageIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageIntegrity->expects($this->once())->method('perform')->will($this->returnValue(true));
        $stageData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageData->expects($this->once())->method('perform')->will($this->returnValue(true));
        $stageVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageVolume->expects($this->once())->method('perform')->will($this->returnValue(true));
        $this->progress->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->logger->expects($this->at(0))->method('info')->with("started");
        $this->logger->expects($this->at(1))->method('info')->with("started");
        $this->logger->expects($this->at(2))->method('info')->with("started");
        $this->logger->expects($this->at(3))->method('info')->with("started");
        $this->logger->expects($this->at(4))->method('info')->with("Migration completed");
        $this->stepList->expects($this->any())->method('getSteps')->willReturn(
            [
                'Title' => [
                    'integrity' => $stageIntegrity,
                    'data' => $stageData,
                    'volume' => $stageVolume
                ]
            ]
        );
        $this->assertTrue($this->data->run());
    }

    /**
     * @return void
     */
    public function testRunStepsWithSuccessProgress()
    {
        $stageIntegrity = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageIntegrity->expects($this->never())->method('perform');
        $stageData = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageData->expects($this->never())->method('perform');
        $stageVolume = $this->getMockBuilder('\Migration\App\Step\StageInterface')->getMock();
        $stageVolume->expects($this->never())->method('perform');
        $this->progress->expects($this->never())->method('saveResult');
        $this->progress->expects($this->any())->method('isCompleted')->willReturn(true);
        $this->logger->expects($this->at(0))->method('info')->with("started");
        $this->logger->expects($this->at(1))->method('info')->with("started");
        $this->logger->expects($this->at(2))->method('info')->with("started");
        $this->logger->expects($this->at(3))->method('info')->with("started");
        $this->logger->expects($this->at(4))->method('info')->with("Migration completed");
        $this->stepList->expects($this->any())->method('getSteps')->willReturn(
            [
                'Title' => [
                    'integrity' => $stageIntegrity,
                    'data' => $stageData,
                    'volume' => $stageVolume
                ]
            ]
        );
        $this->assertTrue($this->data->run());
    }
}
