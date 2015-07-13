<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Handler;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;

/**
 * Class Data
 */
class Data extends \Migration\Step\OrderGrids\Data
{
    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Logger $logger
    ) {
        parent::__construct($progress, $source, $destination, $recordFactory, $logger);
    }

    protected function getSelectSalesOrderGrid($sourceDocument)
    {
        $select = parent::getSelectSalesOrderGrid($sourceDocument);
        $select->joinLeft(['sfoee' => 'sales_flat_order'],
            'sfoee.entity_id = ' . $sourceDocument . '.entity_id',
            'sfoee.customer_bal_total_refunded as refunded_to_store_credit');

        return $select;
    }

    protected function getDocumentList()
    {
        return parent::getDocumentList() /*+ [
            [
                'source' => 'sales_flat_order_grid_archive',
                'destination' => 'sales_order_grid_archive',
                'method' => 'getSelectSalesOrderGrid'
            ], [
                'source' => 'sales_flat_invoice_grid_archive',
                'destination' => 'sales_invoice_grid_archive',
                'method' => 'getSelectSalesInvoiceGrid'
            ], [
                'source' => 'sales_flat_shipment_grid_archive',
                'destination' => 'sales_shipment_grid_archive',
                'method' => 'getSelectSalesShipmentGrid'
            ], [
                'source' => 'sales_flat_creditmemo_grid_archive',
                'destination' => 'sales_creditmemo_grid_archive',
                'method' => 'getSelectSalesCreditmemoGrid'
            ]
        ]*/;
    }
}
