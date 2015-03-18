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
     * @var Factory
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
        $this->factory = new Factory($this->config, $this->objectManager);
    }

    public function testCreate()
    {
        $step = $this->getMock('\Migration\Step\StepInterface');
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($step));
        $this->assertSame($step, $this->factory->create('\Migration\Steps\Integrity'));
    }

    public function testCreateStepWithException()
    {
        $this->setExpectedException('\Exception', 'Class: \Migration\Step\Integrity must implement StepInterface.');
        $this->factory->create('\Migration\Step\Integrity');
    }
}
