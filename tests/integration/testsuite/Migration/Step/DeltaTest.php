<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

/**
 * Delta step test class
 * @dbFixture default
 */
class DeltaTest extends \PHPUnit_Framework_TestCase
{

    public function testSetupTriggers()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/_files/config.xml');
        $integrity = $objectManager->create('\Migration\Step\Map\Integrity');
        $migrate = $objectManager->create('\Migration\Step\Map\Migrate');
        $volume = $objectManager->create('\Migration\Step\Map\Volume');
        $delta = $objectManager-> create('\Migration\Step\Map\Delta');
        /** @var  $source \Migration\Resource\Source */
        $source = $objectManager->create('\Migration\Resource\Source');
        /** @var \Migration\Resource\Adapter\Mysql $mysqlAdapter */

        /** @var \Symfony\Component\Console\Output\ConsoleOutput $progressBar */
        $map = $objectManager->create(
            '\Migration\Step\Map',
            [
                'integrity' => $integrity,
                'migrate' => $migrate,
                'volume' => $volume,
                'delta' => $delta
            ]
        );
        ob_start();
        $this->assertTrue($map->setUpChangeLog());
        ob_end_clean();
        $dataTable = 'table_with_data';
        $changeLogTableName = $source->getChangeLogName($dataTable);
        $changeLogTable = $source->getDocument($changeLogTableName);
        $this->assertEquals($changeLogTableName, $changeLogTable->getName());
        /** @var \Migration\Resource\Document $sourceTables */
        $sourceAdapter = $source->getAdapter();
        $sourceAdapter->insertRecords(
            $dataTable,
            [
                'field1' => 111,
                'field2' => 222,
                'field3' => 333,
            ]
        );
        $sourceAdapter->updateDocument(
            $dataTable,
            [
                'field2' => 122,
                'field3' => 133,
            ],
            'field1 = 111'
        );
        $expectingData = [
            ['id' => '111', 'operation' => 'INSERT'],
            ['id' => '111', 'operation' => 'UPDATE']
        ];
        $this->assertEquals($expectingData, $source->getRecords($changeLogTableName, 0));
    }
}
