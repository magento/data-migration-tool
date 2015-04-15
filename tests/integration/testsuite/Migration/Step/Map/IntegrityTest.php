<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Integrity step test class
 * @dbFixture default
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{

    public function testIntegrityWithMap()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $integrityMap = $objectManager->create('\Migration\Step\Map\Integrity');
        $runMap = $objectManager->create('\Migration\Step\Map\Migrate');
        $volume = $objectManager->create('\Migration\Step\Map\Volume');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderMain');
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Symfony\Component\Console\Output\ConsoleOutput $progressBar */
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
        $map->integrity();
        ob_end_clean();

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
    }

    public function testIntegrityWithoutMap()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config-with-empty-map.xml');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderMain');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $integrityMap = $objectManager->create('\Migration\Step\Map\Integrity', ['mapReader' => $mapReader]);
        $runMap = $objectManager->create('\Migration\Step\Map\Migrate');
        $volume = $objectManager->create('\Migration\Step\Map\Volume');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\Map $map */
        $map = $objectManager->create(
            '\Migration\Step\Map',
            [
                'integrity' => $integrityMap,
                'run' => $runMap,
                'volume' => $volume,
                'logger' => $logger,
                'mapReader' => $mapReader,
                'config' => $config
            ]
        );
        ob_start();
        $map->integrity();
        ob_end_clean();

        $messages = [];
        $messages[] = 'Next documents from source are not mapped:';
        $messages[] = 'source_table_1,source_table_2,source_table_ignored,source_table_renamed'
            .',table_with_data';

        $messages[] = 'Next documents from destination are not mapped:';
        $messages[] = 'dest_table_1,dest_table_2,dest_table_ignored,dest_table_renamed,table_without_data';

        $messages[] = 'Next fields from source are not mapped:';
        $messages[] = 'Document name: common_table; Fields: source_field_ignored';

        $messages[] = 'Next fields from destination are not mapped:';
        $messages[] = 'Document name: common_table; Fields: dest_field_ignored';

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(isset($logOutput[\Monolog\Logger::ERROR]));
        $errors = implode("\n", $logOutput[\Monolog\Logger::ERROR]);

        foreach ($messages as $text) {
            $this->assertContains($text, $errors);
        }
    }
}
