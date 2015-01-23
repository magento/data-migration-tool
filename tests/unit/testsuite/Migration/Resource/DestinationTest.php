<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Resource destination test class
 */
class DestinationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Migration\Resource\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterFactory;

    /**
     * @var \Migration\Resource\Destination
     */
    protected $resourceDestination;

    protected function setUp()
    {
        $config = ['database' => [
            'host' => 'localhost',
            'name' => 'dbname',
            'user' => 'uname',
            'password' => 'upass',
        ]];
        $adapterConfigs = ['config' => [
            'host' => 'localhost',
            'dbname' => 'dbname',
            'username' => 'uname',
            'password' => 'upass',
        ]];
        $this->config = $this->getMock('\Migration\Config', ['getOption', 'getDestination'], [], '', false);
        $this->config->expects($this->once())
            ->method('getOption')
            ->with('bulk_size')
            ->will($this->returnValue(10));
        $this->config->expects($this->once())
            ->method('getDestination')
            ->will($this->returnValue($config));
        $this->adapter = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\Mysql', ['insert', 'query'], [], '', false);
        $this->adapterFactory = $this->getMock('\Migration\Resource\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->resourceDestination = new \Migration\Resource\Destination($this->adapterFactory, $this->config);
    }

    public function testSave()
    {
        $data = [['data1' => 'value']];
        $resourceName = 'core_config_data';
        $this->adapter->expects($this->any())
            ->method('insert')
            ->with($resourceName, ['data1' => 'value'])
            ->will($this->returnSelf());
        $this->resourceDestination->setResourceUnitName($resourceName);
        $this->resourceDestination->save($data);
    }
}
