<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

/**
 * Class StepFactoryTest
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
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
        $this->objectManager = $this->getMockBuilder('\Magento\Framework\ObjectManager\ObjectManager')
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
        $step = $this->getMock('\Migration\App\Step\StageInterface');
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($step));
        $this->assertSame($step, $this->factory->create('\Migration\Steps\Integrity'));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testCreateStepWithException()
    {
        $this->setExpectedException('\Exception', 'Class: \Migration\Step\Integrity must implement StageInterface.');
        $this->factory->create('\Migration\Step\Integrity');
    }
}
