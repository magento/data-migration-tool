<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Steps;

/**
 * Class StepFactoryTest
 */
class StepFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StepFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\
     */
    protected $objectManager;

    /**
     * @var \Migration\Config
     */
    protected $config;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()->getMock();
        $this->objectManager = $this->getMockBuilder('\Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = new StepFactory($this->config, $this->objectManager);
    }

    public function testCreate()
    {
        $step = $this->getMock('\Migration\Steps\StepInterface');
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($step));
        $this->assertSame($step, $this->factory->create('\Migration\Steps\Integrity'));
    }

    public function testCreateStepWithException()
    {
        $this->setExpectedException('\Exception', 'Class: \Migration\Steps\Integrity must implement StepInterface.');
        $this->factory->create('\Migration\Steps\Integrity');
    }

    public function testGetSteps()
    {
        $step = $this->getMock('\Migration\Steps\StepInterface');
        $this->config->expects($this->once())->method('getSteps')->will($this->returnValue(['\Migration\Steps\Test']));
        $this->objectManager->expects($this->once())->method('create')->with('\Migration\Steps\Test')
            ->will($this->returnValue($step));
        $this->assertEquals([], $this->factory->getSteps());
    }
}
