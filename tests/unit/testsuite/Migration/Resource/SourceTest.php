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
     * @var \Migration\Resource\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
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
     * @var int
     */
    protected $bulkSize = 10;

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
            '\Migration\Resource\Adapter\Mysql',
            ['select', 'fetchAll', 'query', 'loadPage', 'createDelta', 'loadChangedRecords', 'loadDeletedRecords'],
            [],
            '',
            false
        );
        $this->adapterFactory = $this->getMock('\Migration\Resource\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->documentFactory = $this->getMock('\Migration\Resource\DocumentFactory', [], [], '', false);
        $this->structureFactory = $this->getMock('\Migration\Resource\StructureFactory', [], [], '', false);
        $this->documentCollection = $this->getMock('\Migration\Resource\Document\Collection', [], [], '', false);
        $this->resourceSource = new \Migration\Resource\Source(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );
    }

    public function testLoadPage()
    {
        $this->config->expects($this->any())
            ->method('getOption')
            ->with('bulk_size')
            ->will($this->returnValue($this->bulkSize));
        $this->adapter->expects($this->any())->method('loadPage')->with('table', 2)->willReturn(['1', '2']);
        $this->assertEquals(['1', '2'], $this->resourceSource->loadPage('table', 2));
    }

    public function testCreateDelta()
    {
        $this->adapter->expects($this->once())->method('createDelta')
            ->with('spfx_document', 'spfx_m2_cl_document', 'key_field');
        $this->config->expects($this->any())->method('getOption')
            ->with(Source::CONFIG_DOCUMENT_PREFIX)
            ->willReturn('spfx_');
        $this->resourceSource->createDelta('document', 'key_field');
    }

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
