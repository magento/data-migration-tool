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

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesOrderGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesOrderGrid($sourceGridDocument, $columns);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesInvoiceGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesInvoiceGrid($sourceGridDocument, $columns);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesShipmentGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesShipmentGrid($sourceGridDocument, $columns);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesCreditmemoGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesCreditmemoGrid($sourceGridDocument, $columns);
    }

    /**
     * @return array
     */
    protected function getDocumentList()
    {
        $documentList = parent::getDocumentList();
        $documentList['getSelectSalesOrderGrid']['columns'] +=
            ['refunded_to_store_credit' => 'sales_order.customer_bal_total_refunded'];
        $documentListArchive = [
            'getSelectSalesOrderGridArchive' => [
                'source' => 'enterprise_sales_order_grid_archive',
                'destination' => 'magento_sales_order_grid_archive',
                'columns' => $documentList['getSelectSalesOrderGrid']['columns']
            ], 'getSelectSalesInvoiceGridArchive'=> [
                'source' => 'enterprise_sales_invoice_grid_archive',
                'destination' => 'magento_sales_invoice_grid_archive',
                'columns' => $documentList['getSelectSalesInvoiceGrid']['columns']
            ], 'getSelectSalesShipmentGridArchive' => [
                'source' => 'enterprise_sales_shipment_grid_archive',
                'destination' => 'magento_sales_shipment_grid_archive',
                'columns' => $documentList['getSelectSalesShipmentGrid']['columns']
            ], 'getSelectSalesCreditmemoGridArchive' => [
                'source' => 'enterprise_sales_creditmemo_grid_archive',
                'destination' => 'magento_sales_creditmemo_grid_archive',
                'columns' => $documentList['getSelectSalesCreditmemoGrid']['columns']
            ]
        ];
        return $documentList + $documentListArchive;
    }
}
