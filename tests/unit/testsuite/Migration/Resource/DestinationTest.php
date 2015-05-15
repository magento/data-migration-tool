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
        $config = [
            'type' => 'database',
            'version' => '2.0.0.0',
            'database' => [
                'host' => 'localhost',
                'name' => 'dbname',
                'user' => 'uname',
                'password' => 'upass',
            ]
        ];
        $adapterConfigs = ['config' => [
            'host' => 'localhost',
            'dbname' => 'dbname',
            'username' => 'uname',
            'password' => 'upass',
        ]];
        $this->config = $this->getMock('\Migration\Config', ['getOption', 'getDestination'], [], '', false);
        $this->config->expects($this->any())
            ->method('getDestination')
            ->will($this->returnValue($config));
        $this->adapter = $this->getMock(
            '\Migration\Resource\Adapter\Mysql',
            ['insertRecords', 'deleteAllRecords', 'backupDocument', 'rollbackDocument', 'deleteBackup'],
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

        $this->resourceDestination = new \Migration\Resource\Destination(
            $this->adapterFactory,
            $this->config,
            $this->documentFactory,
            $this->structureFactory,
            $this->documentCollection
        );
    }

    /**
     * @dataProvider saveRecordsDataSet()
     * @param string|null @prefix
     */
    public function testSaveRecords($prefix)
    {
        $resourceName = 'core_config_data';

        $this->config->expects($this->any())
            ->method('getOption')
            ->willReturnMap([
                ['bulk_size', 3],
                ['dest_prefix', $prefix]
            ]);

        $this->adapter->expects($this->at(0))
            ->method('insertRecords')
            ->with($prefix . $resourceName, [['data' => 'value1'], ['data' => 'value2'], ['data' => 'value3']])
            ->will($this->returnSelf());
        $this->adapter->expects($this->at(1))
            ->method('insertRecords')
            ->with($prefix . $resourceName, [['data' => 'value4']])
            ->will($this->returnSelf());

        $records = $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $records->expects($this->any())
            ->method('current')
            ->willReturnCallback(function () {
                static $count = 0;
                $count++;
                $data = ['data' => "value$count"];
                $record = $this->getMock('\Migration\Resource\Record', ['getData'], [], '', false);
                $record->expects($this->once())->method('getData')->will($this->returnValue($data));
                return $record;
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

    /**
     * @return array
     */
    public function saveRecordsDataSet()
    {
        return [
            ['prefix_'],
            [null]
        ];
    }

    public function testClearDocument()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('deleteAllRecords')->with('pfx_' . $docName);
        $this->config->expects($this->once())->method('getOption')->with('dest_prefix')
            ->will($this->returnValue('pfx_'));
        $this->resourceDestination->clearDocument($docName);
    }

    public function testBackupDocument()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('backupDocument')->with('pfx_' . $docName);
        $this->config->expects($this->once())->method('getOption')->with('dest_prefix')
            ->will($this->returnValue('pfx_'));
        $this->resourceDestination->backupDocument($docName);
    }

    public function testRollbackDocument()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('rollbackDocument')->with('pfx_' . $docName);
        $this->config->expects($this->once())->method('getOption')->with('dest_prefix')
            ->will($this->returnValue('pfx_'));
        $this->resourceDestination->rollbackDocument($docName);
    }

    public function testDeleteDocumentBackup()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('deleteBackup')->with('pfx_' . $docName);
        $this->config->expects($this->once())->method('getOption')->with('dest_prefix')
            ->will($this->returnValue('pfx_'));
        $this->resourceDestination->deleteDocumentBackup($docName);
    }
}
