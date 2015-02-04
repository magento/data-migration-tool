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
     * @var \Migration\Resource\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Migration\Resource\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterFactory;

    /**
     * @var \Migration\Resource\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentFactory;

    /**
     * @var \Migration\Resource\StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureFactory;

    /**
     * @var \Migration\Resource\Document\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentCollection;

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
            ->will($this->returnValue(3));
        $this->config->expects($this->once())
            ->method('getDestination')
            ->will($this->returnValue($config));
        $this->adapter = $this->getMock('\Migration\Resource\Adapter\Mysql', ['insertRecords'], [], '', false);
        $this->adapterFactory = $this->getMock('\Migration\Resource\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->documentFactory = $this->getMock('\Migration\Resource\DocumentFactory', [], [], '', false);
        $this->structureFactory = $this->getMock('\Migration\Resource\StructureFactory', [], [], '', false);
        $this->documentCollection = $this->getMock('\Migration\Resource\Document\Collection', [], [], '', false);

        $this->resourceDestination = new \Migration\Resource\Destination(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );
    }

    public function testSaveRecords()
    {
        $resourceName = 'core_config_data';
        $this->adapter->expects($this->at(0))
            ->method('insertRecords')
            ->with($resourceName, [['data' => 'value1'], ['data' => 'value2'], ['data' => 'value3']])
            ->will($this->returnSelf());
        $this->adapter->expects($this->at(1))
            ->method('insertRecords')
            ->with($resourceName, [['data' => 'value4']])
            ->will($this->returnSelf());
        $records = $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $records->expects($this->any())
            ->method('current')
            ->willReturnCallback(function () {
                static $count = 0;
                $count++;
                return ['data' => "value$count"];
            });
        $records->expects($this->any())
            ->method('valid')
            ->willReturnCallback(function () {
                static $count = 0;
                $count++;
                if ($count <= 4) {
                    return true;
                } else {
                    return false;
                }
            });

        $this->resourceDestination->saveRecords($resourceName, $records);
    }
}
