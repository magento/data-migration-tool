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

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Step\ProgressStep|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $process;

    public function setUp()
    {
        $this->factory = $this->getMockBuilder('\Migration\Step\StepFactory')->disableOriginalConstructor()
            ->setMethods(['getSteps', 'create'])
            ->getMock();
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['info'])
            ->getMock();
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getSteps'])
            ->getMock();
        $this->process = $this->getMockBuilder('\Migration\Step\ProgressStep')->disableOriginalConstructor()
            ->setMethods(['saveResult', 'isCompleted'])
            ->getMock();
        $this->manager = new StepManager($this->process, $this->logger, $this->factory, $this->config);
    }

    public function testRunStepsIntegrityFail()
    {
        $step = $this->getMock(
            '\Migration\Step\StepInterface',
            ['getTitle', 'integrity', 'run', 'volumeCheck'],
            [],
            '',
            false
        );
        $step->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));
        $step->expects($this->once())->method('integrity')->will($this->returnValue(false));
        $step->expects($this->never())->method('run');
        $step->expects($this->never())->method('volumeCheck');
        $this->process->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->process->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->config->expects($this->once())->method('getSteps')->will($this->returnValue([get_class($step)]));
        $this->factory->expects($this->once())->method('create')->with(get_class($step))
            ->will($this->returnValue($step));       
        $this->assertSame($this->manager, $this->manager->runSteps());
    }

    public function testRunStepsVolumeFail()
    {
        $step = $this->getMock(
            '\Migration\Step\StepInterface',
            ['getTitle', 'integrity', 'run', 'volumeCheck'],
            [],
            '',
            false
        );
        $step->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));
        $step->expects($this->once())->method('integrity')->will($this->returnValue(true));
        $step->expects($this->once())->method('run');
        $step->expects($this->once())->method('volumeCheck')->will($this->returnValue(false));
        $this->process->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->process->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->logger->expects($this->never())->method('info');
        $this->config->expects($this->once())->method('getSteps')->will($this->returnValue([get_class($step)]));
        $this->factory->expects($this->once())->method('create')->with(get_class($step))
            ->will($this->returnValue($step));
        $this->assertSame($this->manager, $this->manager->runSteps());
    }

    public function testRunStepsSuccess()
    {
        $step = $this->getMock(
            '\Migration\Step\StepInterface',
            ['getTitle', 'integrity', 'run', 'volumeCheck'],
            [],
            '',
            false
        );
        $step->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));
        $step->expects($this->once())->method('integrity')->will($this->returnValue(true));
        $step->expects($this->once())->method('run');
        $step->expects($this->once())->method('volumeCheck')->will($this->returnValue(true));
        $this->process->expects($this->any())->method('isCompleted')->will($this->returnValue(false));
        $this->process->expects($this->any())->method('saveResult')->willReturnSelf();
        $this->process->expects($this->any())->method('isCompleted')->willReturn(false);
        $this->factory->expects($this->once())->method('getSteps')->will($this->returnValue([$step]));
        $this->logger->expects($this->at(0))->method('info')->with(PHP_EOL . "Title: integrity check");
        $this->logger->expects($this->at(1))->method('info')->with(PHP_EOL . "Title: run");
        $this->logger->expects($this->at(2))->method('info')->with(PHP_EOL . "Title: volume check");
        $this->logger->expects($this->at(3))->method('info')->with(PHP_EOL . "Migration completed");
        $this->config->expects($this->once())->method('getSteps')->will($this->returnValue([get_class($step)]));
        $this->factory->expects($this->once())->method('create')->with(get_class($step))
            ->will($this->returnValue($step));
        $this->assertSame($this->manager, $this->manager->runSteps());
    }

    public function testRunStepsWithSuccessProgress()
    {
        $step = $this->getMock(
            '\Migration\Step\StepInterface',
            ['getTitle', 'integrity', 'run', 'volumeCheck'],
            [],
            '',
            false
        );
        $step->expects($this->any())->method('getTitle')->will($this->returnValue('Title'));
        $step->expects($this->never())->method('integrity')->will($this->returnValue(true));
        $step->expects($this->never())->method('run');
        $step->expects($this->never())->method('volumeCheck')->will($this->returnValue(true));
        $this->process->expects($this->any())->method('isCompleted')->will($this->returnValue(true));
        $this->process->expects($this->never())->method('saveResult');
        $this->process->expects($this->any())->method('isCompleted')->willReturn(true);
        $this->factory->expects($this->once())->method('getSteps')->will($this->returnValue([$step]));
        $this->logger->expects($this->at(0))->method('info')->with(PHP_EOL . "Title: integrity check");
        $this->logger->expects($this->at(1))->method('info')->with("Integrity check completed");
        $this->logger->expects($this->at(2))->method('info')->with(PHP_EOL . "Title: run");
        $this->logger->expects($this->at(3))->method('info')->with("Migration stage completed");
        $this->logger->expects($this->at(4))->method('info')->with(PHP_EOL . "Title: volume check");
        $this->logger->expects($this->at(5))->method('info')->with("Volume check completed");
        $this->logger->expects($this->at(6))->method('info')->with(PHP_EOL . "Migration completed");
        $this->assertSame($this->manager, $this->manager->runSteps());
    }
}
