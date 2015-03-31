<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

/**
 * Class StepFactoryTest
 */
class StepListTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSteps()
    {
        $step1 = $this->getMock('\Migration\App\Step\StepInterface');
        $step2 = $this->getMock('\Migration\App\Step\StepInterface');
        $stepFactory = $this->getMock('\Migration\App\Step\Factory', [], [], '', false);
        $stepFactory->expects($this->any())->method('create')->willReturnMap([
            ['\Migration\Step\Step1', $step1],
            ['\Migration\Step\Step2', $step2]
        ]);
        $config = $this->getMock('\Migration\Config', [], [], '', false);
        $config->expects($this->any())->method('getSteps')->willReturn([
            '\Migration\Step\Step1',
            '\Migration\Step\Step2'
        ]);
        $stepList = new StepList($stepFactory, $config);
        $this->assertEquals([$step1, $step2], $stepList->getSteps('data'));
    }
}
