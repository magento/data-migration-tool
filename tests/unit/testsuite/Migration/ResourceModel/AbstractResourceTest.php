<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

use Migration\Reader\MapInterface;

/**
 * Class AbstractResourceTest
 */
class AbstractResourceTest extends \PHPUnit_Framework_TestCase
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
     * @var \Migration\ResourceModel\Destination
     */
    protected $resourceDestination;

    /**
     * @var \Migration\ResourceModel\Source
     */
    protected $resourceSource;

    /**
     * @return void
     */
    protected function setUp()
    {
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
        $destinationConfig = $config;
        $destinationConfig['version'] = '2.0.0.0';

        $adapterConfigs = ['config' => [
            'database' => [
                'host' => 'localhost',
                'dbname' => 'dbname',
                'username' => 'uname',
                'password' => 'upass',
            ],
            'init_select_parts' => [
                'disable_staging_preview' => true
            ]
        ]];
        $this->config = $this->getMock(
            '\Migration\Config',
            ['getOption', 'getDestination', 'getSource'],
            [],
            '',
            false
        );
        $this->config->expects($this->any())
            ->method('getDestination')
            ->willReturn($destinationConfig);
        $this->config->expects($this->any())
            ->method('getSource')
            ->willReturn($config);
        $this->adapter = $this->getMock(
            '\Migration\ResourceModel\Adapter\Mysql',
            ['insertRecords', 'getRecordsCount', 'getDocumentStructure', 'getDocumentList', 'loadPage'],
            [],
            '',
            false
        );
        $this->adapterFactory = $this->getMock('\Migration\ResourceModel\AdapterFactory', ['create'], [], '', false);
        $this->adapterFactory->expects($this->any())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->documentFactory = $this->getMock(
            '\Migration\ResourceModel\DocumentFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->structureFactory = $this->getMock(
            '\Migration\ResourceModel\StructureFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->documentCollection = $this->getMock(
            '\Migration\ResourceModel\Document\Collection',
            ['addDocument'],
            [],
            '',
            false
        );

        $this->resourceDestination = new \Migration\ResourceModel\Destination(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );

        $this->resourceSource = new \Migration\ResourceModel\Source(
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
     * @param string $optionName
     * @return void
     */
    public function testGetDocument($prefix, $optionName)
    {
        $resourceName = 'core_config_data';
        $structureData = ['id' => 'int'];
        $structure = $this->getMock('\Migration\ResourceModel\Structure', [], [], '', false);
        $document = $this->getMock('\Migration\ResourceModel\Document', [], [], '', false);
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'ce-to-ee'],
            [$optionName, $prefix]
        ]);
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

        $resource = ($prefix == MapInterface::TYPE_SOURCE) ? $this->resourceSource : $this->resourceDestination;
        $this->assertSame($document, $resource->getDocument($resourceName));
    }

    /**
     * @return array
     */
    public function getDocumentDataSource()
    {
        return[
            [MapInterface::TYPE_SOURCE, 'source_prefix'],
            [MapInterface::TYPE_DEST, 'dest_prefix']
        ];
    }

    /**
     * @return void
     */
    public function testGetWrongDocument()
    {
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'ce-to-ee'],
            ['dest_prefix', 'prefix_']
        ]);
        $this->adapter->expects($this->any())
            ->method('getDocumentList')
            ->willReturn(['document']);

        $this->assertFalse($this->resourceDestination->getDocument('badDocument'));
    }

    /**
     * @return void
     */
    public function testGetRecordsCount()
    {
        $prefix = 'prefix_';
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'ce-to-ee'],
            ['dest_prefix', $prefix]
        ]);
        $resourceName = 'core_config_data';
        $this->adapter->expects($this->any())
            ->method('getRecordsCount')
            ->with($prefix . $resourceName)
            ->willReturn(10);

        $this->assertEquals(10, $this->resourceDestination->getRecordsCount($resourceName));
    }

    /**
     * @return void
     */
    public function testGetRecords()
    {
        $resourceName = 'core_config_data';
        $pageNumber = 2;
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'ce-to-ee'],
            ['bulk_size', 100],
            ['dest_prefix', 100],
        ]);
        $this->adapter->expects($this->once())->method('loadPage');
        $this->resourceDestination->getRecords($resourceName, $pageNumber);
    }

    /**
     * @return void
     */
    public function testGetAdapter()
    {
        $this->config->expects($this->once())->method('getOption')->with('edition_migrate')->willReturn('ce-to-ee');
        $this->assertSame($this->adapter, $this->resourceDestination->getAdapter());
    }
}
