<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel\Adapter;

class MysqlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdoMysql;

    /**
     * @var \Migration\ResourceModel\Adapter\Mysql
     */
    protected $adapterMysql;

    /**
     * @return void
     */
    protected function setUp()
    {

        $this->pdoMysql = $this->getMockBuilder(\Magento\Framework\DB\Adapter\Pdo\Mysql::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'truncateTable',
                'query',
                'describeTable',
                'listTables',
                'fetchOne',
                'fetchAll',
                'insertMultiple',
                'select',
                'createTable',
                'dropTable',
                'resetDdlCache',
                'createTableByDdl',
                'update',
                'isTableExists',
                'insertFromSelect',
                'getConnection'
            ])
            ->getMock();
        $this->pdoMysql->expects($this->any())->method('query');
        $mysqlBuilder = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Pdo\MysqlBuilder::class,
            ['build']
        );
        $mysqlBuilder->expects($this->any())
            ->method('build')
            ->with('source')
            ->willReturn($this->pdoMysql);

        $triggerFactory = $this->createPartialMock(
            \Magento\Framework\DB\Ddl\TriggerFactory::class,
            ['create']
        );
        $this->adapterMysql = new Mysql($mysqlBuilder, $triggerFactory, 'source');
    }

    /**
     * @return void
     */
    public function testGetDocumentStructure()
    {
        $this->pdoMysql->expects($this->any())
            ->method('describeTable')
            ->with('some_table')
            ->willReturn(['id' => 'int']);
        $this->assertEquals(['id' => 'int'], $this->adapterMysql->getDocumentStructure('some_table'));
    }

    /**
     * @return void
     */
    public function testGetDocumentList()
    {
        $this->pdoMysql->expects($this->any())
            ->method('listTables')
            ->willReturn(['table1', 'table2']);
        $this->assertEquals(['table1', 'table2'], $this->adapterMysql->getDocumentList());
    }

    /**
     * @return void
     */
    public function testGetRecordsCount()
    {
        $select = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['from']
        );
        $select->expects($this->any())
            ->method('from')
            ->with($this->equalTo('some_table'), $this->equalTo('COUNT(*)'));

        $this->pdoMysql->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $this->pdoMysql->expects($this->any())
            ->method('fetchOne')
            ->with($this->equalTo($select))
            ->willReturn(10);

        $this->assertEquals(10, $this->adapterMysql->getRecordsCount('some_table'));
    }

    /**
     * @return void
     */
    public function testLoadPage()
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $select->expects($this->any())
            ->method('from')
            ->with($this->equalTo('some_table'), $this->equalTo('*'))
            ->willReturnSelf();
        $select->expects($this->any())
            ->method('limit')
            ->with($this->equalTo(2), $this->equalTo(20));

        $this->pdoMysql->expects($this->any())
            ->method('select')
            ->willReturn($select);
        $data = [['column1' => 'value1'], ['column1' => 'value2']];
        $this->pdoMysql->expects($this->any())
            ->method('fetchAll')
            ->with($this->equalTo($select))
            ->willReturn($data);

        $this->assertEquals($data, $this->adapterMysql->loadPage('some_table', 10, 2));
    }

    /**
     * @return void
     */
    public function testInsertRecords()
    {
        $data = [['column1' => 'value1'], ['column1' => 'value2']];

        $pdoMock = $this->getMockBuilder(\PdoTest::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepare', 'execute'])
            ->getMock();
        $pdoMock->expects($this->any())->method('prepare')->willReturnSelf();
        $pdoMock->expects($this->any())->method('execute')->willReturnSelf();
        $this->pdoMysql->expects($this->any())->method('getConnection')->willReturn($pdoMock);
        $this->assertEquals(2, $this->adapterMysql->insertRecords('some_table', $data));
    }

    /**
     * @return void
     */
    public function testDeleteAllRecords()
    {
        $docName = 'some_name';
        $this->pdoMysql->expects($this->once())->method('truncateTable')->with($docName);
        $this->adapterMysql->deleteAllRecords($docName);
    }

    /**
     * @return void
     */
    public function testGetSelect()
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->pdoMysql->expects($this->any())->method('select')->willReturn($select);
        $this->assertSame($select, $this->adapterMysql->getSelect());
    }

    /**
     * @return void
     */
    public function testLoadDataFromSelect()
    {
        $select = $this->createMock(\Magento\Framework\DB\Select::class);
        $data = [['id' => 1], ['id' => 2]];
        $this->pdoMysql->expects($this->any())->method('fetchAll')->with($select)->willReturn($data);
        $this->assertSame($data, $this->adapterMysql->loadDataFromSelect($select));
    }

    /**
     * @return void
     */
    public function testUpdateDocument()
    {
        $docName = 'some_name';
        $condition = 'field1 = 1';
        $this->pdoMysql->expects($this->once())->method('update')->with($docName, [], $condition);
        $this->adapterMysql->updateDocument($docName, [], $condition);
    }

    /**
     * @return void
     */
    public function testGetTableDdlCopy()
    {
        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->pdoMysql->expects($this->once())->method('createTableByDdl')
            ->with('source_table', 'destination_table')
            ->will($this->returnValue($table));
        $this->adapterMysql->getTableDdlCopy('source_table', 'destination_table');
    }

    /**
     * @return void
     */
    public function testCreateTableByDdl()
    {
        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $table->expects($this->exactly(2))->method('getName')->will($this->returnValue('some_name'));
        $this->pdoMysql->expects($this->once())->method('dropTable')->with('some_name');
        $this->pdoMysql->expects($this->once())->method('createTable')->with($table);
        $this->pdoMysql->expects($this->once())->method('resetDdlCache')->with('some_name');
        $this->adapterMysql->createTableByDdl($table);
    }

    /**
     * @return void
     */
    public function testBackupDocument()
    {
        $documentName = 'document_name';
        $backupDocumentName = 'migration_backup_document_name';

        $table = $this->getMockBuilder(\Magento\Framework\DB\Ddl\Table::class)->disableOriginalConstructor()
            ->setMethods(['getName'])
            ->getMock();
        $table->expects($this->any())->method('getName')->will($this->returnValue('migration_backup_document_name'));
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)->disableOriginalConstructor()
            ->setMethods(['from'])->getMock();
        $select->expects($this->once())->method('from')->with($documentName)->willReturn($select);

        $this->pdoMysql->expects($this->once())->method('createTableByDdl')
            ->with($documentName, $backupDocumentName)
            ->will($this->returnValue($table));
        $this->pdoMysql->expects($this->once())->method('isTableExists')->willReturn(false);
        $this->pdoMysql->expects($this->once())->method('dropTable')->with($backupDocumentName);
        $this->pdoMysql->expects($this->once())->method('createTable')->with($table);
        $this->pdoMysql->expects($this->once())->method('resetDdlCache')->with($backupDocumentName);
        $this->pdoMysql->expects($this->once())->method('select')->willReturn($select);
        $this->pdoMysql->expects($this->once())->method('insertFromSelect')->with($select, $backupDocumentName)
            ->willReturn('select query');
        $this->pdoMysql->expects($this->once())->method('query')->with('select query');

        $this->adapterMysql->backupDocument($documentName);
    }

    /**
     * @return void
     */
    public function testRollbackDocument()
    {
        $documentName = 'document_name';
        $backupDocumentName = 'migration_backup_document_name';

        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)->disableOriginalConstructor()
            ->setMethods(['from'])->getMock();
        $select->expects($this->once())->method('from')->with($backupDocumentName)->willReturn($select);

        $this->pdoMysql->expects($this->once())->method('isTableExists')->willReturn(true);
        $this->pdoMysql->expects($this->once())->method('truncateTable')->with($documentName);
        $this->pdoMysql->expects($this->once())->method('select')->willReturn($select);
        $this->pdoMysql->expects($this->once())->method('insertFromSelect')->with($select, $documentName)
            ->willReturn('select query');
        $this->pdoMysql->expects($this->once())->method('query')->with('select query');
        $this->pdoMysql->expects($this->once())->method('dropTable')->with($backupDocumentName);

        $this->adapterMysql->rollbackDocument($documentName);
    }

    /**
     * @return void
     */
    public function testDeleteBackup()
    {
        $this->pdoMysql->expects($this->once())->method('isTableExists')->willReturn(true);
        $this->pdoMysql->expects($this->once())->method('dropTable')->with('migration_backup_document_name');
        $this->adapterMysql->deleteBackup('document_name');
    }
}
