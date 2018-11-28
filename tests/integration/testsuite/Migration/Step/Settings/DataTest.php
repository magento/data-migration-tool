<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Settings;

/**
 * Settings Data step test class
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testPerform()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $recordFactory = $objectManager->create(\Migration\ResourceModel\RecordFactory::class);
        $progress = $objectManager->create(\Migration\App\ProgressBar\LogLevelProcessor::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $mapReader = $objectManager->create(\Migration\Reader\Settings::class);
        $handlerManagerFactory = $objectManager->get(\Migration\Handler\ManagerFactory::class);
        $destination = $objectManager->get(\Migration\ResourceModel\Destination::class);
        $source = $objectManager->get(\Migration\ResourceModel\Source::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();
        $data = $objectManager->create(
            \Migration\Step\Settings\Data::class,
            [
                'destination' => $destination,
                'source' => $source,
                'logger' => $logger,
                'progress' => $progress,
                'recordFactory' => $recordFactory,
                'mapReader' => $mapReader,
                'handlerManagerFactory' => $handlerManagerFactory,
            ]
        );
        ob_start();
        $data->perform();
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
