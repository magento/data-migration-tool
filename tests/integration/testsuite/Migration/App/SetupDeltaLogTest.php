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

    public function testSetupTriggers()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        /** @var \Migration\Resource\Source $source */
        $source = $objectManager->create('\Migration\Resource\Source');
        /** @var \Migration\App\SetupDeltaLog $setupDeltaLog */
        $setupDeltaLog = $objectManager->create(
            '\Migration\App\SetupDeltaLog'
        );

        ob_start();
        $this->assertTrue($setupDeltaLog->perform());
        ob_end_clean();

        $dataTable = 'table_with_data';
        $deltaLogTableName = $source->getDeltaLogName($dataTable);
        $deltaLogTable = $source->getDocument($deltaLogTableName);
        $this->assertEquals($deltaLogTableName, $deltaLogTable->getName());
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
        $expectingData = [
            ['field1' => '100', 'operation' => 'UPDATE'],
            ['field1' => '101', 'operation' => 'INSERT']
        ];
        $this->assertEquals($expectingData, $source->getRecords($deltaLogTableName, 0));
    }
}
