<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

/**
 * Class StepFactoryTest
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StageFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = new StageFactory($this->objectManager);
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testCreate()
    {
        $step = $this->createMock(\Migration\App\Step\StageInterface::class);
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($step));
        $this->assertSame($step, $this->factory->create(\Migration\Steps\Integrity::class));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testCreateStepWithException()
    {
        $this->expectException('\Exception');
        $this->expectExceptionMessage('Class: Migration\Step\Integrity must implement StageInterface.');
        $this->factory->create(\Migration\Step\Integrity::class);
    }
}
