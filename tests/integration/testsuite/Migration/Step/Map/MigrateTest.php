<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Migrate step test class
 */
class MigrateTest extends \PHPUnit_Framework_TestCase
{
    public function testPerform()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $integrityMap = $objectManager->create('\Migration\Step\Map\Integrity');
        $runMap = $objectManager->create('\Migration\Step\Map\Migrate');
        $volume = $objectManager->create('\Migration\Step\Map\Volume');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderMain');
        $config = $objectManager->get('\Migration\Config');
        $destination = $objectManager->get('\Migration\Resource\Destination');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);
        \Migration\Logger\Logger::clearMessages();

        $map = $objectManager->create(
            '\Migration\Step\Map',
            [
                'integrity' => $integrityMap,
                'run' => $runMap,
                'volume' => $volume,
                'logger' => $logger,
                'map' => $mapReader,
                'config' => $config
            ]
        );
        ob_start();
        $map->run();
        ob_end_clean();

        $migratedData = $destination->getRecords('table_without_data', 0);
        $migratedDataExpected = [
            ['field1' => 1, 'field2' => 2, 'field3' => 3],
            ['field1' => 2, 'field2' => 3, 'field3' => 4],
            ['field1' => 3, 'field2' => 4, 'field3' => 5],
            ['field1' => 4, 'field2' => 5, 'field3' => 6],
            ['field1' => 5, 'field2' => 5, 'field3' => 5],
            ['field1' => 6, 'field2' => 6, 'field3' => 7],
            ['field1' => 7, 'field2' => 7, 'field3' => 7]
        ];
        $migratedDataIgnored = $destination->getRecords('table_ignored', 0);
        $migratedDataIgnoredExpected = [];
        $migratedDataPresetValue = $destination->getRecords('common_table', 0);
        $migratedDataPresetValueExpected = [
            ['key' => 1, 'dest_field_ignored' => 0, 'common_field' => 123],
            ['key' => 2, 'dest_field_ignored' => 0, 'common_field' => 123],
            ['key' => 3, 'dest_field_ignored' => 0, 'common_field' => 123],
            ['key' => 4, 'dest_field_ignored' => 0, 'common_field' => 123],
            ['key' => 5, 'dest_field_ignored' => 0, 'common_field' => 123],
            ['key' => 6, 'dest_field_ignored' => 0, 'common_field' => 123],
            ['key' => 7, 'dest_field_ignored' => 0, 'common_field' => 123]
        ];

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
        $this->assertEquals($migratedDataExpected, $migratedData);
        $this->assertEquals($migratedDataIgnoredExpected, $migratedDataIgnored);
        $this->assertEquals($migratedDataPresetValueExpected, $migratedDataPresetValue);
    }
}
