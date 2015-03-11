<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\UrlRewrite;

/**
 * Migrate step test class
 */
class Version11410to2000Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\UrlRewrite
     */
    protected $urlRewrite;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->tableName = 'url_rewrite_m2' . md5('url_rewrite_m2');
        $this->objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $this->objectManager->get('\Migration\Config')->init(__DIR__ . '/../_files/config.xml');
        $logManager = $this->objectManager->create('\Migration\Logger\Manager');
        $logger = $this->objectManager->create('\Migration\Logger\Logger');
        $config = $this->objectManager->get('\Migration\Config');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\UrlRewrite $urlRewrite */
        $this->urlRewrite = $this->objectManager->create(
            '\Migration\Step\UrlRewrite',
            [
                'logger' => $logger,
                'config' => $config
            ]
        );
    }

    public function testIntegrity()
    {
        ob_start();
        $result = $this->urlRewrite->integrity();
        ob_end_clean();
        $this->assertTrue($result);

        $messages = [];
        $messages[] = 'There are duplicates in URL rewrites';
        $messages[] = 'Request path: test1.html Store ID: 1 Target path: catalog/category/view/id/6';
        $messages[] = 'Request path: test1.html Store ID: 1 Target path: contacts';
        $messages[] = 'Request path: test1.html Store ID: 1 Target path: catalog/category/view/id/6';

        $messages[] = 'Request path: test5.html Store ID: 1 Target path: contacts';
        $messages[] = 'Request path: test5.html Store ID: 1 Target path: catalog/category/view/id/8';

        $messages[] = 'Request path: test1.html Store ID: 2 Target path: catalog/category/view/id/6';
        $messages[] = 'Request path: test1.html Store ID: 2 Target path: catalog/category/view/id/6';

        $messages[] = 'Request path: test1.html Store ID: 3 Target path: catalog/category/view/id/6';
        $messages[] = 'Request path: test1.html Store ID: 3 Target path: catalog/category/view/id/6';

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(empty($logOutput[\Monolog\Logger::INFO]));
        $errors = implode("\n", $logOutput[\Monolog\Logger::INFO]);

        foreach ($messages as $text) {
            $this->assertContains($text, $errors);
        }
    }

    public function testRun()
    {
        /** @var \Migration\Resource\Destination $destination */
        $destination = $this->objectManager->get('\Migration\Resource\Destination');
        ob_start();
        $this->urlRewrite->run();
        ob_end_clean();

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertTrue(empty($logOutput[\Monolog\Logger::ERROR]));
        $this->assertEquals(42, $destination->getRecordsCount('url_rewrite'));
        $this->assertEquals(11, $destination->getRecordsCount('catalog_category_entity_varchar'));
        $this->assertEquals(4, $destination->getRecordsCount('catalog_product_entity_varchar'));
    }
}
