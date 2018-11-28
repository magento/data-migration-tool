<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\UrlRewrite;

/**
 * UrlRewrite step test class
 * @dbFixture url_rewrite_11410
 */
class Version11410to2000Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var \Migration\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Migration\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @throws \Migration\Exception
     * @return void
     */
    protected function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $this->objectManager = $helper->getObjectManager();
        $this->objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->tableName = 'url_rewrite_m2' . md5('url_rewrite_m2');
        $logManager = $this->objectManager->create(\Migration\Logger\Manager::class);
        $this->logger = $this->objectManager->create(\Migration\Logger\Logger::class);
        $this->logger->pushHandler($this->objectManager->create(\Migration\Logger\ConsoleHandler::class));
        $this->config = $this->objectManager->get(\Migration\Config::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();
    }

    /**
     * @return void
     */
    public function testIntegrity()
    {
        $urlRewrite = $this->objectManager->create(
            \Migration\Step\UrlRewrite\Version11410to2000::class,
            [
                'logger' => $this->logger,
                'config' => $this->config,
                'stage' => 'integrity'
            ]
        );
        ob_start();
        $result = $urlRewrite->perform();
        ob_end_clean();
        $this->assertTrue($result);

        $messages = [];
        $messages[] = 'There are duplicates in URL rewrites';
        $messages[] = 'Request path: test1.html Store ID: 1 Target path: catalog/category/view/id/6';
        $messages[] = 'Request path: test1.html Store ID: 1 Target path: contacts';
        $messages[] = 'Request path: test5.html Store ID: 1 Target path: contacts';
        $messages[] = 'Request path: test5.html Store ID: 1 Target path: catalog/category/view/id/8';

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(empty($logOutput[\Monolog\Logger::INFO]));
        $errors = implode("\n", $logOutput[\Monolog\Logger::INFO]);

        foreach ($messages as $text) {
            $this->assertContains($text, $errors);
        }
    }

    /**
     * @return void
     */
    public function testData()
    {
        $urlRewrite = $this->objectManager->create(
            \Migration\Step\UrlRewrite\Version11410to2000::class,
            [
                'logger' => $this->logger,
                'config' => $this->config,
                'stage' => 'data'
            ]
        );
        /** @var \Migration\ResourceModel\Destination $destination */
        $destination = $this->objectManager->get(\Migration\ResourceModel\Destination::class);
        ob_start();
        $urlRewrite->perform();
        ob_end_clean();

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(empty($logOutput[\Monolog\Logger::ERROR]));
        $this->assertEquals(48, $destination->getRecordsCount('url_rewrite'));
        $this->assertEquals(11, $destination->getRecordsCount('catalog_category_entity_varchar'));
        $this->assertEquals(4, $destination->getRecordsCount('catalog_product_entity_varchar'));

        $urlRewrite = $this->objectManager->create(
            \Migration\Step\UrlRewrite\Version11410to2000::class,
            [
                'logger' => $this->logger,
                'config' => $this->config,
                'stage' => 'volume'
            ]
        );
        $result = $urlRewrite->perform();
        $this->assertTrue($result);
    }
}
