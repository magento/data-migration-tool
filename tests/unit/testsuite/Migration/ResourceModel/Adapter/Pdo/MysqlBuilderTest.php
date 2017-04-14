<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel\Adapter\Pdo;

class MysqlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdoMysql;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\DB\SelectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectFactory;

    /**
     * @var MysqlBuilder
     */
    protected $mysqlBuilder;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = $this->getMockBuilder('\Magento\Framework\ObjectManager\ObjectManager')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->config = $this->getMockBuilder('Migration\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getResourceConfig', 'getOption'])
            ->getMock();
        $this->selectFactory = $this->getMockBuilder('Magento\Framework\DB\SelectFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->pdoMysql = $this->getMockBuilder('\Magento\Framework\DB\Adapter\Pdo\Mysql')
            ->disableOriginalConstructor()
            ->setMethods(['disallowDdlCache', 'query'])
            ->getMock();
        $this->mysqlBuilder = new MysqlBuilder($this->objectManager, $this->config);
    }

    /**
     * @return void
     */
    public function testBuild()
    {
        $resourceType = 'source';
        $initStatements = 'SET NAMES utf8;';
        $resourceConfig = [
            'type' => $resourceType,
            $resourceType => [
                'host' => 'localhost',
                'port' => '9999',
                'name' => 'db1',
                'user' => 'root',
                'password' => 'root'
            ],
        ];
        $mysqlPdoConfig = [
            'host' => 'localhost',
            'port' => '9999',
            'dbname' => 'db1',
            'username' => 'root',
            'password' => 'root'
        ];
        $this->config->expects($this->once())
            ->method('getResourceConfig')
            ->with($resourceType)
            ->willReturn($resourceConfig);
        $this->config->expects($this->any())->method('getOption')->willReturnMap([
            ['edition_migrate', 'ce-to-ce'],
            ['init_statements_' . $resourceType, $initStatements]
        ]);
        $this->objectManager->expects($this->at(0))
            ->method('create')
            ->with('\Magento\Framework\DB\SelectFactory', ['parts' => []])
            ->willReturn($this->selectFactory);
        $this->objectManager->expects($this->at(1))
            ->method('create')
            ->with(
                '\Magento\Framework\DB\Adapter\Pdo\Mysql',
                ['config' => $mysqlPdoConfig, 'selectFactory' => $this->selectFactory]
            )
            ->willReturn($this->pdoMysql);
        $this->pdoMysql->expects($this->once())
            ->method('disallowDdlCache')
            ->willReturnSelf();
        $this->pdoMysql->expects($this->once())
            ->method('query')
            ->with($initStatements)
            ->willReturnSelf();

        $this->assertEquals($this->pdoMysql, $this->mysqlBuilder->build($resourceType));
    }
}
