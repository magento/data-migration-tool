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
class VolumeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Migration\Exception
     * @return void
     */
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
        $objectManager->get(\Migration\Config::class)->init(dirname(__DIR__) . '/../_files/config.xml');
        $logManager = $objectManager->create(\Migration\Logger\Manager::class);
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $config = $objectManager->get(\Migration\Config::class);
        $initialData = $objectManager->get(\Migration\Step\SalesOrder\InitialData::class);
        $destination = $objectManager->get(\Migration\ResourceModel\Destination::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();

        $data = $objectManager->create(
            \Migration\Step\SalesOrder\Data::class,
            [
                'logger' => $logger,
                'config' => $config,
                'initialData' => $initialData
            ]
        );
        $volume = $objectManager->create(
            \Migration\Step\SalesOrder\Volume::class,
            [
                'logger' => $logger,
                'config' => $config,
                'initialData' => $initialData
            ]
        );
        ob_start();
        $data->perform();
        $this->assertTrue($volume->perform());
        ob_end_clean();

        $this->assertEquals($eavAttributesToMigrate, $destination->getRecords('eav_entity_int', 0));
        $this->assertEquals($salesOrderToMigrate, $destination->getRecords('sales_order', 0));
        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
    }
}
