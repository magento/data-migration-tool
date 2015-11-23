<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

/**
 * Adapter Factory Test
 */
class AdapterFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\ResourceModel\AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMock('\Migration\Config', ['getOption'], [], '', false);
        $this->objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );
        $this->adapterFactory = new \Migration\ResourceModel\AdapterFactory($this->objectManager, $this->config);
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        $adapterClassName = '\Migration\ResourceModel\Adapter\Mysql';
        $data = ['config' => ['key' => 'value']];
        $adapter = $this->getMock($adapterClassName, [], [], '', false);
        $this->config->expects($this->once())
            ->method('getOption')
            ->with('resource_adapter_class_name')
            ->will($this->returnValue(null));
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with('\Migration\ResourceModel\Adapter\Mysql', $data)
            ->will($this->returnValue($adapter));
        $this->assertInstanceOf($adapterClassName, $this->adapterFactory->create($data));
    }
}
