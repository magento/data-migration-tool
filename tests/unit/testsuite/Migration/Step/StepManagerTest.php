<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

/**
 * Class StepManagerTest
 */
class StepManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StepManager
     */
    protected $manager;

    /**
     * @var \Migration\Step\StepFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->factory = $this->getMockBuilder('\Migration\Step\StepFactory')->disableOriginalConstructor()
            ->setMethods(['getSteps'])
            ->getMock();
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['info'])
            ->getMock();
        $this->manager = new StepManager($this->logger, $this->factory);
    }

    public function testRunStepsIntegrityFail()
    {
        $step = $this->getMock('\Migration\Step\StepInterface', [], [], '', false);
        $step->expects($this->once())->method('integrity')->will($this->returnValue(false));
        $step->expects($this->never())->method('run');
        $step->expects($this->never())->method('volumeCheck');
        $this->factory->expects($this->once())->method('getSteps')->will($this->returnValue([$step]));
        $this->assertSame($this->manager, $this->manager->runSteps());
    }

    public function testRunStepsVolumeFail()
    {
        $step = $this->getMock('\Migration\Step\StepInterface', [], [], '', false);
        $step->expects($this->once())->method('integrity')->will($this->returnValue(true));
        $step->expects($this->once())->method('run');
        $step->expects($this->once())->method('volumeCheck')->will($this->returnValue(false));
        $this->factory->expects($this->once())->method('getSteps')->will($this->returnValue([$step]));
        $this->logger->expects($this->never())->method('info');
        $this->assertSame($this->manager, $this->manager->runSteps());
    }

    public function testRunStepsSuccess()
    {
        $step = $this->getMock('\Migration\Step\StepInterface', [], [], '', false);
        $step->expects($this->once())->method('integrity')->will($this->returnValue(true));
        $step->expects($this->once())->method('run');
        $step->expects($this->once())->method('volumeCheck')->will($this->returnValue(true));
        $this->factory->expects($this->once())->method('getSteps')->will($this->returnValue([$step]));
        $this->logger->expects($this->once())->method('info')->with(PHP_EOL . "Migration completed");
        $this->assertSame($this->manager, $this->manager->runSteps());
    }
}
