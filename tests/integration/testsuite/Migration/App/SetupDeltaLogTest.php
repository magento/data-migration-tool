<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\App;

/**
 * Delta step test class
 */
class SetupDeltaLogTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return void
     */
    public function testSetupTriggers()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        /** @var \Migration\ResourceModel\Source $source */
        $source = $objectManager->create('\Migration\ResourceModel\Source');
        /** @var \Migration\App\SetupDeltaLog $setupDeltaLog */
        $setupDeltaLog = $objectManager->create(
            '\Migration\App\SetupDeltaLog'
        );

        ob_start();
        $this->assertTrue($setupDeltaLog->perform());
        ob_end_clean();

        $dataTable = 'table_with_data';
        $this->checkDeltaLogTable($dataTable, $source);
        $this->checkDeltaLogTable('source_table_1', $source);
        $this->checkDeltaLogTable('source_table_2', $source);

        $sourceAdapter = $source->getAdapter();
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
        $this->assertEquals($expectedData, $source->getRecords($source->getDeltaLogName($dataTable), 0));
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
}
