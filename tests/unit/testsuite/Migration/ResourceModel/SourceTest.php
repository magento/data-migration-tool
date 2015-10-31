<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

/**
 * ResourceModel source test class
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\ResourceModel\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Migration\ResourceModel\AdapterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterFactory;

    /**
     * @var \Migration\ResourceModel\Source
     */
    protected $resourceSource;

    /**
     * @var \Migration\ResourceModel\DocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentFactory;

    /**
     * @var \Migration\ResourceModel\StructureFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structureFactory;

    /**
     * @var \Migration\ResourceModel\Document\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentCollection;

    /**
     * @var int
     */
    protected $bulkSize = 10;

    /**
     * @return void
     */
    protected function setUp()
    {
        $adapterConfigs = ['config' => [
            'host' => 'localhost',
            'dbname' => 'dbname',
            'username' => 'uname',
            'password' => 'upass',
        ]];
        $this->config = $this->getMock('\Migration\Config', ['getOption', 'getSource'], [], '', false);
        $config = [
            'type' => 'database',
            'version' => '1.14.1.0',
            'database' => [
                'host' => 'localhost',
                'name' => 'dbname',
                'user' => 'uname',
                'password' => 'upass',
            ]
        ];
        $this->config->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue($config));
        $this->adapter = $this->getMock(
            '\Migration\ResourceModel\Adapter\Mysql',
            ['select', 'fetchAll', 'query', 'loadPage', 'createDelta', 'loadChangedRecords', 'loadDeletedRecords'],
            [],
            '',
            false
        );
        $this->adapterFactory = $this->getMock('\Migration\ResourceModel\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->documentFactory = $this->getMock('\Migration\ResourceModel\DocumentFactory', [], [], '', false);
        $this->structureFactory = $this->getMock('\Migration\ResourceModel\StructureFactory', [], [], '', false);
        $this->documentCollection = $this->getMock('\Migration\ResourceModel\Document\Collection', [], [], '', false);
        $this->resourceSource = new \Migration\ResourceModel\Source(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );
    }

    /**
     * @return void
     */
    public function testLoadPage()
    {
        $this->config->expects($this->any())
            ->method('getOption')
            ->with('bulk_size')
            ->will($this->returnValue($this->bulkSize));
        $this->adapter->expects($this->any())->method('loadPage')->with('table', 2)->willReturn(['1', '2']);
        $this->assertEquals(['1', '2'], $this->resourceSource->loadPage('table', 2));
    }

    /**
     * @return void
     */
    public function testCreateDelta()
    {
        $this->adapter->expects($this->once())->method('createDelta')
            ->with('spfx_document', 'spfx_m2_cl_document', 'key_field');
        $this->config->expects($this->any())->method('getOption')
            ->with(Source::CONFIG_DOCUMENT_PREFIX)
            ->willReturn('spfx_');
        $this->resourceSource->createDelta('document', 'key_field');
    }

    /**
     * @return void
     */
    public function testGetChangedRecords()
    {
        $this->adapter->expects($this->once())->method('loadChangedRecords')
            ->with('document', 'm2_cl_document', 'key_field', 0, 100);
        $this->config->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['source_prefix', ''],
                ['bulk_size', 100]
            ]
        );
        $this->resourceSource->getChangedRecords('document', 'key_field');
    }

    /**
     * @return void
     */
    public function testGetDeletedRecords()
    {
        $this->adapter->expects($this->once())->method('loadDeletedRecords')
            ->with('m2_cl_document', 'key_field', 0, 100);
        $this->config->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['source_prefix', ''],
                ['bulk_size', 100]
            ]
        );
        $this->resourceSource->getDeletedRecords('document', 'key_field');
    }
}
