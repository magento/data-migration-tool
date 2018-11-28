<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

/**
 * SalesOrder step run test class
 * @dbFixture sales_order
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Migration\Exception
     * @return void
     */
    public function testPerform()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get(\Migration\Config::class)->init(dirname(__DIR__) . '/../_files/config.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $config = $objectManager->get(\Migration\Config::class);
        $initialData = $objectManager->get(\Migration\Step\SalesOrder\InitialData::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        /** @var \Migration\Step\SalesOrder\Integrity $salesOrder */
        $salesOrder = $objectManager->create(
            \Migration\Step\SalesOrder\Integrity::class,
            [
                'logger' => $logger,
                'config' => $config,
                'initialData' => $initialData
            ]
        );
        ob_start();
        $salesOrder->perform();
        ob_end_clean();

        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
    }
}
