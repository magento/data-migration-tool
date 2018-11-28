<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

/**
 * ResourceModel destination test class
 */
class DestinationTest extends \PHPUnit\Framework\TestCase
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
     * @return void
     */
    protected function setUp()
    {
        $adapterConfigs = ['resourceType' => 'destination'];
        $this->config = $this->createPartialMock(
            \Migration\Config::class,
            ['getOption']
        );
        $this->adapter = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Mysql::class,
            ['insertRecords', 'deleteAllRecords', 'backupDocument', 'rollbackDocument', 'deleteBackup']
        );
        $this->adapterFactory = $this->createPartialMock(
            \Migration\ResourceModel\AdapterFactory::class,
            ['create']
        );
        $this->adapterFactory->expects($this->once())
            ->method('create')
            ->with($adapterConfigs)
            ->will($this->returnValue($this->adapter));
        $this->documentFactory = $this->createMock(\Migration\ResourceModel\DocumentFactory::class);
        $this->structureFactory = $this->createMock(\Migration\ResourceModel\StructureFactory::class);
        $this->documentCollection = $this->createMock(\Migration\ResourceModel\Document\Collection::class);

        $this->resourceDestination = new \Migration\ResourceModel\Destination(
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
     * @return void
     */
    public function testSaveRecords($prefix)
    {
        $resourceName = 'core_config_data';

        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'opensource-to-commerce'],
            ['bulk_size', 3],
            ['dest_prefix', $prefix],
            ['init_statements_destination', 'SET NAMES utf8;']
        ]);
        $this->adapter->expects($this->at(0))
            ->method('insertRecords')
            ->with($prefix . $resourceName, [['data' => 'value1'], ['data' => 'value2'], ['data' => 'value3']])
            ->will($this->returnSelf());
        $this->adapter->expects($this->at(1))
            ->method('insertRecords')
            ->with($prefix . $resourceName, [['data' => 'value4']])
            ->will($this->returnSelf());

        $records = $this->createMock(\Migration\ResourceModel\Record\Collection::class);
        $records->expects($this->any())
            ->method('current')
            ->willReturnCallback(function () {
                static $count = 0;
                $count++;
                $data = ['data' => "value$count"];
                $record = $this->createPartialMock(
                    \Migration\ResourceModel\Record::class,
                    ['getData']
                );
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

    /**
     * @return void
     */
    public function testClearDocument()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('deleteAllRecords')->with('pfx_' . $docName);
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'opensource-to-commerce'],
            ['dest_prefix', 'pfx_'],
            ['init_statements_destination', 'SET NAMES utf8;']
        ]);
        $this->resourceDestination->clearDocument($docName);
    }

    /**
     * @return void
     */
    public function testBackupDocument()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('backupDocument')->with('pfx_' . $docName);
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'opensource-to-commerce'],
            ['dest_prefix', 'pfx_'],
            ['init_statements_destination', 'SET NAMES utf8;']
        ]);
        $this->resourceDestination->backupDocument($docName);
    }

    /**
     * @return void
     */
    public function testRollbackDocument()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('rollbackDocument')->with('pfx_' . $docName);
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'opensource-to-commerce'],
            ['dest_prefix', 'pfx_'],
            ['init_statements_destination', 'SET NAMES utf8;']
        ]);
        $this->resourceDestination->rollbackDocument($docName);
    }

    /**
     * @return void
     */
    public function testDeleteDocumentBackup()
    {
        $docName = 'somename';
        $this->adapter->expects($this->once())->method('deleteBackup')->with('pfx_' . $docName);
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'opensource-to-commerce'],
            ['dest_prefix', 'pfx_'],
            ['init_statements_destination', 'SET NAMES utf8;']
        ]);
        $this->resourceDestination->deleteDocumentBackup($docName);
    }
}
