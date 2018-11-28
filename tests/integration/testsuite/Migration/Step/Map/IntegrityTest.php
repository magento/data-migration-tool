<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Integrity step test class
 * @dbFixture default
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testIntegrityWithMap()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $logger->pushHandler($objectManager->create(\Migration\Logger\ConsoleHandler::class));
        $config = $objectManager->get(\Migration\Config::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\Map\Integrity $map */
        $map = $objectManager->create(
            \Migration\Step\Map\Integrity::class,
            [
                'logger' => $logger,
                'config' => $config
            ]
        );
        ob_start();
        $map->perform();
        ob_end_clean();
        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(isset($logOutput[\Monolog\Logger::ERROR]));

        $config->setOption(\Migration\Config::OPTION_AUTO_RESOLVE, 1);
        \Migration\Logger\Logger::clearMessages();
        ob_start();
        $map->perform();
        ob_end_clean();
        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(isset($logOutput[\Monolog\Logger::WARNING]));
    }

    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testIntegrityWithoutMap()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get(\Migration\Config::class)->init(dirname(__DIR__) . '/../_files/config-with-empty-map.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $logger->pushHandler($objectManager->create(\Migration\Logger\ConsoleHandler::class));
        $config = $objectManager->get(\Migration\Config::class);
        $config->setOption(\Migration\Config::OPTION_AUTO_RESOLVE, 0);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\Map\Integrity $map */
        $map = $objectManager->create(
            \Migration\Step\Map\Integrity::class,
            [
                'logger' => $logger,
                'config' => $config
            ]
        );
        ob_start();
        $map->perform();
        ob_end_clean();

        $messages = [];
        $messages[] = 'Source documents are not mapped: ';
        $messages[] = 'source_table_1,source_table_2,source_table_ignored,source_table_renamed,table_with_data';

        $messages[] = 'Destination documents are not mapped: ';
        $messages[] = 'dest_table_1,dest_table_2,dest_table_ignored,dest_table_renamed,table_without_data';

        $messages[] = 'Source fields are not mapped. ';
        $messages[] = 'Document: common_table. Fields: source_field_ignored';

        $messages[] = 'Destination fields are not mapped. ';
        $messages[] = 'Document: common_table. Fields: dest_field_ignored';

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(isset($logOutput[\Monolog\Logger::ERROR]));
        $errors = implode("\n", $logOutput[\Monolog\Logger::ERROR]);

        foreach ($messages as $text) {
            $this->assertContains($text, $errors);
        }
    }
}
