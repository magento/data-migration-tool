<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Resource source test class
 */
class SourceTest extends \PHPUnit_Framework_TestCase
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
     * @var \Migration\Resource\Source
     */
    protected $resourceSource;

    /**
     * @var int
     */
    protected $bulkSize = 10;

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
        $this->config = $this->getMock('\Migration\Config', ['getOption', 'getSource'], [], '', false);
        $this->config->expects($this->once())
            ->method('getOption')
            ->with('bulk_size')
            ->will($this->returnValue($this->bulkSize));
        $this->config->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue($config));
        $this->adapter = $this->getMock(
            '\Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'fetchAll', 'query'],
            [],
            '',
            false
        );
        $this->adapterFactory = $this->getMock('\Migration\Resource\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->resourceSource = new \Migration\Resource\Source($this->adapterFactory, $this->config);
    }

    public function testGetNextBunch()
    {
        $resourceName = 'core_config_data';
        $position = 5;
        $data = ['key' => 'value'];
        $select = $this->getMock('\Magento\Framework\DB\Select', ['from', 'limit'], [], '', false);
        $select->expects($this->once())
            ->method('from')
            ->with($resourceName, '*')
            ->will($this->returnSelf());
        $select->expects($this->once())
            ->method('limit')
            ->with($this->bulkSize, $position)
            ->will($this->returnSelf());
        $this->adapter->expects($this->once())
            ->method('select')
            ->will($this->returnValue($select));
        $this->adapter->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->will($this->returnValue($data));
        $this->resourceSource->setPosition($position);
        $this->resourceSource->setResourceUnitName($resourceName);
        $this->assertEquals($data, $this->resourceSource->getNextBunch());
        $this->assertEquals($this->bulkSize + $position, $this->resourceSource->getPosition());
    }
}
