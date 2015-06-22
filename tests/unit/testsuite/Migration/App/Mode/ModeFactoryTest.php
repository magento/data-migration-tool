<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Mode;

/**
 * Class StepFactoryTest
 */
class ModeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModeFactory
     */
    protected $modeFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder('\Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->modeFactory = new ModeFactory($this->objectManager);
    }

    public function testCreate()
    {
        $mode = $this->getMock('\Migration\App\Mode\ModeInterface');
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($mode));
        $this->assertSame($mode, $this->modeFactory->create('data'));
    }

    public function testCreateStepWithException()
    {
        $this->setExpectedException('\Exception', "Mode 'mode' does not exist.");
        $this->modeFactory->create('mode');
    }

    public function testCreateStepWithException2()
    {
        $mode = $this->getMock('Migration\Mode\Unknown', [], [], '', false);
        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($mode));

        $this->setExpectedException('\Migration\Exception', 'Mode class must implement ModeInterface.');
        $this->modeFactory->create('unknown');
    }
}
