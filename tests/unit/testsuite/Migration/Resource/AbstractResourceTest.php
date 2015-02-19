<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

class AbstractResourceTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @var \Migration\Resource\Source
     */
    protected $resourceSource;

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
        $this->config = $this->getMock(
            '\Migration\Config',
            ['getOption', 'getDestination', 'getSource'],
            [],
            '',
            false
        );
        $this->config->expects($this->once())
            ->method('getDestination')
            ->will($this->returnValue($config));
        $this->config->expects($this->once())
            ->method('getSource')
            ->will($this->returnValue($config));
        $this->adapter = $this->getMock(
            '\Migration\Resource\Adapter\Mysql',
            ['insertRecords', 'getRecordsCount', 'getDocumentStructure', 'getDocumentList'],
            [],
            '',
            false
        );
        $this->adapterFactory = $this->getMock('\Migration\Resource\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->any())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->documentFactory = $this->getMock(
            '\Migration\Resource\DocumentFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->structureFactory = $this->getMock('\Migration\Resource\StructureFactory', ['create'], [], '', false);
        $this->documentCollection = $this->getMock(
            '\Migration\Resource\Document\Collection',
            ['addDocument'],
            [],
            '',
            false
        );

        $this->resourceDestination = new \Migration\Resource\Destination(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );

        $this->resourceSource = new \Migration\Resource\Source(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );
    }

    /**
     * @dataProvider getDocumentDataSource()
     * @param string $prefix
     * @param string $type
     */
    public function testGetDocument($prefix, $type)
    {
        $resourceName = 'core_config_data';
        $structureData = ['id' => 'int'];
        $structure = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $document = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $this->config->expects($this->any())
            ->method('getOption')
            ->with($type)
            ->will($this->returnValue($prefix));
        $this->documentFactory->expects($this->any())
            ->method('create')
            ->with($this->equalTo(['structure' => $structure, 'documentName' => $resourceName]))
            ->will($this->returnValue($document));
        $this->adapter->expects($this->any())
            ->method('getDocumentStructure')
            ->with($this->equalTo($prefix . $resourceName))
            ->willReturn($structureData);
        $this->structureFactory->expects($this->any())
            ->method('create')
            ->with($this->equalTo(['documentName' => $resourceName, 'data' => $structureData]))
            ->willReturn($structure);
        $this->adapter->expects($this->any())
            ->method('getDocumentList')
            ->willReturn([$prefix . $resourceName]);

        $resource = ($prefix == 'source') ? $this->resourceSource : $this->resourceDestination;
        $this->assertSame($document, $resource->getDocument($resourceName));
    }

    /**
     * @return array
     */
    public function getDocumentDataSource()
    {
        return[
            ['source', 'source_prefix'],
            ['destination', 'dest_prefix']
        ];
    }

    public function testGetWrongDocument()
    {
        $prefix = 'prefix_';
        $this->config->expects($this->any())
            ->method('getOption')
            ->with('dest_prefix')
            ->will($this->returnValue($prefix));
        $this->adapter->expects($this->any())
            ->method('getDocumentList')
            ->willReturn(['document']);

        $this->assertFalse($this->resourceDestination->getDocument('badDocument'));
    }

    public function testGetRecordsCount()
    {
        $prefix = 'prefix_';
        $this->config->expects($this->any())
            ->method('getOption')
            ->with('dest_prefix')
            ->will($this->returnValue($prefix));
        $resourceName = 'core_config_data';

        $this->adapter->expects($this->any())
            ->method('getRecordsCount')
            ->with($prefix . $resourceName)
            ->willReturn(10);

        $this->assertEquals(10, $this->resourceDestination->getRecordsCount($resourceName));
    }
}
