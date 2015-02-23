<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite;

/**
 * Class VersionFactoryTest
 */
class VersionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VersionFactory
     */
    protected $factory;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = $this->getMockBuilder('\Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory = new VersionFactory($this->objectManager);
    }

    public function testCreate()
    {
        $step = $this->getMock('\Migration\Step\StepInterface');
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with('Migration\Step\UrlRewrite\Version11410to2000')
            ->will($this->returnValue($step));
        $this->assertSame($step, $this->factory->create('1.14.1.0', '2.0.0.0'));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Class: Migration\Step\UrlRewrite\Version11410to2000 must implement StepInterface.
     */
    public function testCreateWithException()
    {
        $this->factory->create('1.14.1.0', '2.0.0.0');
    }
}
