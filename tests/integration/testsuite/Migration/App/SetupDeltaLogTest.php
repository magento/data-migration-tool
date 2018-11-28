<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Delta step test class
 */
class SetupDeltaLogTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var SetupDeltaLog|\PHPUnit_Framework_MockObject_MockObject
     */
    private $setupDeltaLog;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Migration\ResourceModel\Source
     */
    private $source;

    /**
     * @return void
     */
    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $this->objectManager = $helper->getObjectManager();
        $this->objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->setupDeltaLog = $this->objectManager->create(
            \Migration\App\SetupDeltaLog::class
        );
        $this->source = $this->objectManager->create(\Migration\ResourceModel\Source::class);
    }

    /**
     * @return void
     */
    public function testSetupTriggers()
    {
        ob_start();
        $this->assertTrue($this->setupDeltaLog->perform());
        ob_end_clean();

        $dataTable = 'table_with_data';
        $this->checkDeltaLogTable($dataTable, $this->source);
        $this->checkDeltaLogTable('source_table_1', $this->source);
        $this->checkDeltaLogTable('source_table_2', $this->source);

        $sourceAdapter = $this->source->getAdapter();
        $sourceAdapter->insertRecords(
            $dataTable,
            [
                'field1' => 100,
                'field2' => 2,
                'field3' => 3,
            ]
        );
        $sourceAdapter->insertRecords(
            $dataTable,
            [
                'field1' => 1000,
                'field2' => 200
            ]
        );
        $sourceAdapter->insertRecords(
            $dataTable,
            [
                'field1' => 101,
                'field2' => 22,
                'field3' => 33,
            ]
        );
        $sourceAdapter->updateDocument(
            $dataTable,
            [
                'field2' => 12,
                'field3' => 13,
            ],
            'field1 = 100'
        );
        $expectedData = [
            ['key' => '8', 'operation' => 'UPDATE', 'processed' => 0],
            ['key' => '9', 'operation' => 'INSERT', 'processed' => 0],
            ['key' => '10', 'operation' => 'INSERT', 'processed' => 0]
        ];
        $this->assertEquals($expectedData, $this->source->getRecords($this->source->getDeltaLogName($dataTable), 0));
    }

    /**
     * @param string $dataTable
     * @param \Migration\ResourceModel\Source $resource
     * @return void
     */
    protected function checkDeltaLogTable($dataTable, $resource)
    {
        $deltaLogTableName = $resource->getDeltaLogName($dataTable);
        $deltaLogTable = $resource->getDocument($deltaLogTableName);
        $this->assertEquals($deltaLogTableName, $deltaLogTable->getName());
    }

    /**
     * @return void
     */
    public function testSetupTriggersFail()
    {
        $message = [
            \Monolog\Logger::WARNING => ['Some of the delta log tables were not created. Expected:3. Actual:2']
        ];
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->source->getAdapter()->getSelect()->getAdapter();
        $adapter->dropTable('source_table_1');
        ob_start();
        $this->assertTrue($this->setupDeltaLog->perform());
        ob_end_clean();
        $this->assertEquals($message, \Migration\Logger\Logger::getMessages());
    }
}
