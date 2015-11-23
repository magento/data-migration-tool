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

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testIntegrityWithMap()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $logger->pushHandler($objectManager->create('\Migration\Logger\ConsoleHandler'));
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\Map\Integrity $map */
        $map = $objectManager->create(
            '\Migration\Step\Map\Integrity',
            [
                'logger' => $logger,
                'config' => $config
            ]
        );
        ob_start();
        $map->perform();
        ob_end_clean();

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testIntegrityWithoutMap()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config-with-empty-map.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $logger->pushHandler($objectManager->create('\Migration\Logger\ConsoleHandler'));
        $config = $objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\Map\Integrity $map */
        $map = $objectManager->create(
            '\Migration\Step\Map\Integrity',
            [
                'logger' => $logger,
                'config' => $config
            ]
        );
        ob_start();
        $map->perform();
        ob_end_clean();

        $messages = [];
        $messages[] = 'Source documents are missing or not mapped: ';
        $messages[] = 'source_table_1,source_table_2,source_table_ignored,source_table_renamed,table_with_data';

        $messages[] = 'Destination documents are missing or not mapped: ';
        $messages[] = 'dest_table_1,dest_table_2,dest_table_ignored,dest_table_renamed,table_without_data';

        $messages[] = 'Source fields are missing or not mapped. ';
        $messages[] = 'Document: common_table. Fields: source_field_ignored';

        $messages[] = 'Destination fields are missing or not mapped. ';
        $messages[] = 'Document: common_table. Fields: dest_field_ignored';

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(isset($logOutput[\Monolog\Logger::ERROR]));
        $errors = implode("\n", $logOutput[\Monolog\Logger::ERROR]);

        foreach ($messages as $text) {
            $this->assertContains($text, $errors);
        }
    }
}
