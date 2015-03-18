<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Run;

/**
 * SalesOrder step run test class
 * @dbFixture sales_order
 */
class SalesOrderTest extends \PHPUnit_Framework_TestCase
{
    public function testPerform()
    {
        $salesOrderToMigrate = [
            [
                'entity_id' => '1',
                'store_id' => '1'
            ],
            [
                'entity_id' => '2',
                'store_id' => '1'
            ],
            [
                'entity_id' => '3',
                'store_id' => '1'
            ]
        ];

        $eavAttributesToMigrate = [
            [
                'value_id' => '1',
                'entity_type_id' => '1',
                'attribute_id' => '1',
                'store_id' => '1',
                'entity_id' => '1',
                'value' => '1'
            ],
            [
                'value_id' => '2',
                'entity_type_id' => '1',
                'attribute_id' => '2',
                'store_id' => '1',
                'entity_id' => '1',
                'value' => '2'
            ],
            [
                'value_id' => '3',
                'entity_type_id' => '1',
                'attribute_id' => '1',
                'store_id' => '1',
                'entity_id' => '2',
                'value' => '1'
            ],
            [
                'value_id' => '4',
                'entity_type_id' => '1',
                'attribute_id' => '2',
                'store_id' => '1',
                'entity_id' => '3',
                'value' => '2'
            ]
        ];
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/../_files/config.xml');
        $logManager = $objectManager->create('\Migration\Logger\Manager');
        $integritySalesOrder = $objectManager->create('\Migration\Step\Integrity\SalesOrder');
        $runSalesOrder = $objectManager->create('\Migration\Step\Run\SalesOrder');
        $volumeSalesOrder = $objectManager->create('\Migration\Step\Volume\SalesOrder');
        $logger = $objectManager->create('\Migration\Logger\Logger');
        $mapReader = $objectManager->create('\Migration\MapReader\MapReaderSalesOrder');
        $config = $objectManager->get('\Migration\Config');
        $initialData = $objectManager->get('\Migration\Step\SalesOrder\InitialData');
        $destination = $objectManager->get('\Migration\Resource\Destination');
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_NONE);
        \Migration\Logger\Logger::clearMessages();

        $salesOrder = $objectManager->create(
            '\Migration\Step\SalesOrder',
            [
                'integrity' => $integritySalesOrder,
                'run' => $runSalesOrder,
                'volume' => $volumeSalesOrder,
                'logger' => $logger,
                'map' => $mapReader,
                'config' => $config,
                'initialData' => $initialData
            ]
        );
        ob_start();
        $salesOrder->run();
        ob_end_clean();

        $this->assertEquals($eavAttributesToMigrate, $destination->getRecords('eav_entity_int', 0));
        $this->assertEquals($salesOrderToMigrate, $destination->getRecords('sales_order', 0));
        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
    }
}
