<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

/**
 * SalesIncrement step run test class
 * @dbFixture sales_increment
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private $salesSequenceMeta = [
        [
            'meta_id' => 1,
            'entity_type' => 'order',
            'store_id' => 0,
            'sequence_table' => 'sequence_order_0',
        ],
        [
            'meta_id' => 2,
            'entity_type' => 'order',
            'store_id' => 1,
            'sequence_table' => 'sequence_order_1',
        ],
        [
            'meta_id' => 3,
            'entity_type' => 'invoice',
            'store_id' => 0,
            'sequence_table' => 'sequence_invoice_0',
        ],
        [
            'meta_id' => 4,
            'entity_type' => 'invoice',
            'store_id' => 1,
            'sequence_table' => 'sequence_invoice_1',
        ],
        [
            'meta_id' => 5,
            'entity_type' => 'creditmemo',
            'store_id' => 0,
            'sequence_table' => 'sequence_creditmemo_0',
        ],
        [
            'meta_id' => 6,
            'entity_type' => 'creditmemo',
            'store_id' => 1,
            'sequence_table' => 'sequence_creditmemo_1',
        ],
        [
            'meta_id' => 7,
            'entity_type' => 'shipment',
            'store_id' => 0,
            'sequence_table' => 'sequence_shipment_0',
        ],
        [
            'meta_id' => 8,
            'entity_type' => 'shipment',
            'store_id' => 1,
            'sequence_table' => 'sequence_shipment_1',
        ],
        [
            'meta_id' => 9,
            'entity_type' => 'rma_item',
            'store_id' => 0,
            'sequence_table' => 'sequence_rma_item_0',
        ],
        [
            'meta_id' => 10,
            'entity_type' => 'rma_item',
            'store_id' => 1,
            'sequence_table' => 'sequence_rma_item_1',
        ]
    ];
    
    /**
     * @var array
     */
    private $salesSequenceProfile = [
        [
            'profile_id' => 1,
            'meta_id' => 1,
            'prefix' => '',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 2,
            'meta_id' => 2,
            'prefix' => '1',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 3,
            'meta_id' => 3,
            'prefix' => '',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 4,
            'meta_id' => 4,
            'prefix' => '1',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 5,
            'meta_id' => 5,
            'prefix' => '',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 6,
            'meta_id' => 6,
            'prefix' => '1',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 7,
            'meta_id' => 7,
            'prefix' => '',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 8,
            'meta_id' => 8,
            'prefix' => '1',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 9,
            'meta_id' => 9,
            'prefix' => '',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ],
        [
            'profile_id' => 10,
            'meta_id' => 10,
            'prefix' => '1',
            'suffix' => '',
            'start_value' => 1,
            'step' => 1,
            'max_value' => 4294967295,
            'warning_value' => 4294966295,
            'is_active' => 1,
        ]
    ];
    
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
        $logger = $objectManager->create(\Migration\Logger\Logger::class);
        $config = $objectManager->get(\Migration\Config::class);
        $helper = $objectManager->get(\Migration\Step\SalesIncrement\Helper::class);
        $destination = $objectManager->get(\Migration\ResourceModel\Destination::class);
        /** @var \Migration\Logger\Manager $logManager */
        $logManager->process(\Migration\Logger\Manager::LOG_LEVEL_ERROR);
        \Migration\Logger\Logger::clearMessages();
        /** @var \Migration\Step\SalesIncrement\Data $salesIncrement */
        $salesIncrement = $objectManager->create(
            \Migration\Step\SalesIncrement\Data::class,
            [
                'logger' => $logger,
                'config' => $config,
                'helper' => $helper
            ]
        );
        ob_start();
        $salesIncrement->perform();
        ob_end_clean();

        $this->assertEquals($this->salesSequenceMeta, $destination->getRecords('sales_sequence_meta', 0));
        $this->assertEquals($this->salesSequenceProfile, $destination->getRecords('sales_sequence_profile', 0));
        $logOutput = \Migration\Logger\Logger::getMessages();
        $this->assertFalse(isset($logOutput[\Monolog\Logger::ERROR]));
    }
}
