<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

/**
 * Class StepFactoryTest
 */
class StepListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testGetSteps()
    {
        $stage1 = $this->createMock(\Migration\App\Step\StageInterface::class);
        $stage2 = $this->createMock(\Migration\App\Step\StageInterface::class);
        $stepFactory = $this->createMock(\Migration\App\Step\StageFactory::class);
        $stepFactory->expects($this->any())->method('create')->willReturnMap([
            ['\Migration\Step\Stage1', ['stage' => 'stage1'], $stage1],
            ['\Migration\Step\Stage2', ['stage' => 'stage2'], $stage2]
        ]);
        $config = $this->createMock(\Migration\Config::class);
        $config->expects($this->any())->method('getSteps')->with('mode')->willReturn([
            'Test Step' => [
                'stage1' => '\Migration\Step\Stage1',
                'stage2' => '\Migration\Step\Stage2',
            ]
        ]);
        $stepList = new StepList($stepFactory, $config, 'mode');
        $this->assertEquals(['Test Step' => ['stage1' => $stage1, 'stage2' => $stage2]], $stepList->getSteps());
    }
}
