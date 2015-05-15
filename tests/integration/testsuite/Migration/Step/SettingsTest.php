<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Map;

/**
 * Settings step test class
 */
class SettingsTest extends \PHPUnit_Framework_TestCase
{
    public function testData()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $recordFactory = $objectManager->create('\Migration\Resource\RecordFactory');
        $progress = $objectManager->create('\Migration\App\ProgressBar\LogLevelProcessor');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\Reader\Settings');
        $handlerManagerFactory = $objectManager->get('\Migration\Handler\ManagerFactory');
        $destination = $objectManager->get('\Migration\Resource\Destination');
        $source = $objectManager->get('\Migration\Resource\Source');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();
        $settings = $objectManager->create(
            '\Migration\Step\Settings',
            [
                'destination' => $destination,
                'source' => $source,
                'logger' => $logger,
                'progress' => $progress,
                'recordFactory' => $recordFactory,
                'mapReader' => $mapReader,
                'handlerManagerFactory' => $handlerManagerFactory,
                'stage' => 'data'
            ]
        );
        ob_start();
        $settings->perform();
        ob_end_clean();
        $migratedData = $destination->getRecords('core_config_data', 0);
        $migratedDataExpected = [
            [
                'config_id' => 1,
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'web/seo/use_rewrites',
                'value' => 1
            ], [
                'config_id' => 2,
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'web/unsecure/base_url',
                'value' => 'http://magento2.dev/'
            ], [
                'config_id' => 3,
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'admin/security/session_lifetime',
                'value' => 90
            ], [
                'config_id' => 4,
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'catalog/seo/product_url_suffix',
                'value' => '.phtml'
            ], [
                'config_id' => 5,
                'scope' => 'default',
                'scope_id' => 0,
                'path' => 'my/extension/path',
                'value' => 'value1'
            ]
        ];
        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
        $this->assertEquals($migratedDataExpected, $migratedData);
    }
}
