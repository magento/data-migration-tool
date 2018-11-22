<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Data step test class
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testPerform()
    {
        $progress = $this->createPartialMock(
            \Migration\App\Progress::class,
            ['getProcessedEntities', 'addProcessedEntity']
        );
        $progress->expects($this->once())->method('getProcessedEntities')->will($this->returnValue([]));
        $progress->expects($this->any())->method('addProcessedEntity');

        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $logger->pushHandler($objectManager->create(\Migration\Logger\ConsoleHandler::class));
        $config = $objectManager->get(\Migration\Config::class);
        $destination = $objectManager->get(\Migration\ResourceModel\Destination::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        $map = $objectManager->create(
            \Migration\Step\Map\Data::class,
            [
                'logger' => $logger,
                'config' => $config,
                'progress' => $progress
            ]
        );
        ob_start();
        $map->perform();
        ob_end_clean();

        $migratedData = $destination->getRecords('table_without_data', 0);
        $migratedDataExpected = [
            ['key' => 1, 'field1' => 1, 'field2' => 2, 'field3' => 3],
            ['key' => 2, 'field1' => 2, 'field2' => 3, 'field3' => 4],
            ['key' => 3, 'field1' => 3, 'field2' => 4, 'field3' => 5],
            ['key' => 4, 'field1' => 4, 'field2' => 5, 'field3' => 6],
            ['key' => 5, 'field1' => 5, 'field2' => 5, 'field3' => 5],
            ['key' => 6, 'field1' => 6, 'field2' => 6, 'field3' => 7],
            ['key' => 7, 'field1' => 7, 'field2' => 7, 'field3' => 7]
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
