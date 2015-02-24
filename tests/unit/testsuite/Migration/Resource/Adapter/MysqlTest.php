<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource\Adapter;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdoMysql;

    /**
     * @var \Migration\Resource\Adapter\Mysql
     */
    protected $adapterMysql;

    protected function setUp()
    {
        $config = [
            'host' => 'localhost',
            'dbname' => 'dbname',
            'username' => 'uname',
            'password' => 'upass',
        ];
        $adapterConfigs = ['config' => [
            'host' => 'localhost',
            'dbname' => 'dbname',
            'username' => 'uname',
            'password' => 'upass',
        ]];
        $this->pdoMysql = $this->getMock(
            '\Magento\Framework\DB\Adapter\Pdo\Mysql',
            [
                'truncateTable',
                'query',
                'describeTable',
                'listTables',
                'fetchOne',
                'fetchAll',
                'insertMultiple',
                'select'
            ],
            [],
            '',
            false
        );
        $this->pdoMysql->expects($this->any())->method('query');
        $mysqlFactory = $this->getMock('\Magento\Framework\DB\Adapter\Pdo\MysqlFactory', ['create'], [], '', false);
        $mysqlFactory->expects($this->any())
            ->method('create')
            ->with($adapterConfigs)
            ->willReturn($this->pdoMysql);

        $this->adapterMysql = new \Migration\Resource\Adapter\Mysql($mysqlFactory, $config);
    }

    public function testGetDocumentStructure()
    {
        $this->pdoMysql->expects($this->any())
            ->method('describeTable')
            ->with('some_table')
            ->willReturn(['id' => 'int']);
        $this->assertEquals(['id' => 'int'], $this->adapterMysql->getDocumentStructure('some_table'));
    }

    public function testGetDocumentList()
    {
        $this->pdoMysql->expects($this->any())
            ->method('listTables')
            ->willReturn(['table1', 'table2']);
        $this->assertEquals(['table1', 'table2'], $this->adapterMysql->getDocumentList());
    }

    public function testGetRecordsCount()
    {
        $select = $this->getMock('\Magento\Framework\DB\Select', ['from'], [], '', false);
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

    public function testLoadPage()
    {
        $select = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
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

    public function testInsertRecords()
    {
        $data = [['column1' => 'value1'], ['column1' => 'value2']];

        $this->pdoMysql->expects($this->any())
            ->method('insertMultiple')
            ->with($this->equalTo('some_table'), $this->equalTo($data))
            ->willReturn(2);

        $this->assertEquals(2, $this->adapterMysql->insertRecords('some_table', $data));
    }

    public function testDeleteAllRecords()
    {
        $docName = 'some_name';
        $this->pdoMysql->expects($this->once())->method('truncateTable')->with($docName);
        $this->adapterMysql->deleteAllRecords($docName);
    }

    public function testGetSelect()
    {
        $select = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $this->pdoMysql->expects($this->any())->method('select')->willReturn($select);
        $this->assertSame($select, $this->adapterMysql->getSelect());
    }

    public function testLoadDataFromSelect()
    {
        $select = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);
        $data = [['id' => 1], ['id' => 2]];
        $this->pdoMysql->expects($this->any())->method('fetchAll')->with($select)->willReturn($data);
        $this->assertSame($data, $this->adapterMysql->loadDataFromSelect($select));
    }
}
