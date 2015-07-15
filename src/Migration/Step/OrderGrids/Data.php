<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\Resource\Adapter\Mysql;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Mysql
     */
    protected $sourceAdapter;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

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
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->destination = $destination;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        foreach ($this->getDocumentList() as $methodToExecute => $document) {
            $sourceDocumentName = $document['source'];
            $destinationDocumentName = $document['destination'];
            $columns = $document['columns'];
            $destinationDocument = $this->destination->getDocument($destinationDocumentName);
            $this->destination->clearDocument($destinationDocumentName);
            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocumentName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocumentName), LogManager::LOG_LEVEL_DEBUG);
            /** @var \Magento\Framework\DB\Select $select */
            $select = call_user_func_array([$this, $methodToExecute], [$sourceDocumentName, $columns]);
            while (!empty($bulk = $this->getRecords($select, $pageNumber))) {
                $pageNumber++;
                $destinationCollection = $destinationDocument->getRecords();
                foreach ($bulk as $recordData) {
                    $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                    /** @var Record $destinationRecord */
                    $destinationRecord = $this->recordFactory->create(
                        ['document' => $destinationDocument, 'data' => $recordData]
                    );
                    $destinationCollection->addRecord($destinationRecord);
                }
                $this->destination->saveRecords($destinationDocumentName, $destinationCollection);
                $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
            }
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * @return int
     */
    protected function getIterationsCount()
    {
        $iterations = 0;
        foreach ($this->getDocumentList() as $document) {
            $iterations += $this->source->getRecordsCount($document['source']);
        }
        return $iterations;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $pageNumber
     * @return array
     */
    protected function getRecords(\Magento\Framework\DB\Select $select, $pageNumber)
    {
        $select->limit($this->source->getPageSize(), $pageNumber * $this->source->getPageSize());
        return $this->sourceAdapter->loadDataFromSelect($select);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesOrderGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_order' => 'sales_flat_order'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_order.entity_id', [])
            ->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_order.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_order.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesInvoiceGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_invoice' => 'sales_flat_invoice'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_invoice.entity_id', [])
            ->joinLeft(
                ['sales_order' => 'sales_flat_order'],
                'sales_invoice.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_invoice.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_invoice.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesShipmentGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_shipment' => 'sales_flat_shipment'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_shipment.entity_id', [])
            ->joinLeft(
                ['sales_order' => 'sales_flat_order'],
                'sales_shipment.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_shipment.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_shipment.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesCreditmemoGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_creditmemo' => 'sales_flat_creditmemo'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_creditmemo.entity_id', [])
            ->joinLeft(
                ['sales_order' => 'sales_flat_order'],
                'sales_creditmemo.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_creditmemo.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_creditmemo.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @return array
     */
    protected function getDocumentList()
    {
        return [
            'getSelectSalesOrderGrid' => [
                'source' => 'sales_flat_order_grid',
                'destination' => 'sales_order_grid',
                'columns' => [
                    'entity_id' => 'sales_order.entity_id',
                    'status' => 'sales_order.status',
                    'store_id' => 'sales_order.store_id',
                    'store_name' => 'sales_order.store_name',
                    'customer_id' => 'sales_order.customer_id',
                    'base_grand_total' => 'sales_order.base_grand_total',
                    'base_total_paid' => 'sales_order.base_total_paid',
                    'grand_total' => 'sales_order.grand_total',
                    'total_paid' => 'sales_order.total_paid',
                    'increment_id' => 'sales_order.increment_id',
                    'base_currency_code' => 'sales_order.base_currency_code',
                    'order_currency_code' => 'sales_order.order_currency_code',
                    'shipping_name' => 'trim(concat(ifnull(sales_shipping_address.firstname, \'\'), \' \' '
                        .',ifnull(sales_shipping_address.lastname, \'\')))',
                    'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                        .',ifnull(sales_billing_address.lastname, \'\')))',
                    'created_at' => 'sales_order.created_at',
                    'updated_at' => 'sales_order.updated_at',
                    'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                        .',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region,'
                        .' \'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
                    'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                        .',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region,'
                        .' \'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
                    'shipping_information' => 'sales_order.shipping_description',
                    'customer_email' => 'sales_order.customer_email',
                    'customer_group' => 'sales_order.customer_group_id',
                    'subtotal' => 'sales_order.base_subtotal',
                    'shipping_and_handling' => 'sales_order.base_shipping_amount',
                    'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                        .',ifnull(sales_order.customer_lastname, \'\')))',
                    'payment_method' => '(SELECT `sales_order_payment`.`method` FROM `sales_flat_order_payment` '
                        .'as sales_order_payment WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
                    'total_refunded' => 'sales_order.total_refunded',
                ]
            ], 'getSelectSalesInvoiceGrid' => [
                'source' => 'sales_flat_invoice_grid',
                'destination' => 'sales_invoice_grid',
                'columns' => [
                    'entity_id' => 'sales_invoice.entity_id',
                    'increment_id' => 'sales_invoice.increment_id',
                    'state' => 'sales_invoice.state',
                    'store_id' => 'sales_invoice.store_id',
                    'store_name' => 'sales_order.store_name',
                    'order_id' => 'sales_invoice.order_id',
                    'order_increment_id' => 'sales_order.increment_id',
                    'order_created_at' => 'sales_order.created_at',
                    'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                        .',ifnull(sales_order.customer_lastname, \'\')))',
                    'customer_email' => 'sales_order.customer_email',
                    'customer_group_id' => 'sales_order.customer_group_id',
                    'payment_method' => '(SELECT `sales_order_payment`.`method` FROM `sales_flat_order_payment` '
                        .'as sales_order_payment WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
                    'store_currency_code' => 'sales_invoice.store_currency_code',
                    'order_currency_code' => 'sales_invoice.order_currency_code',
                    'base_currency_code' => 'sales_invoice.base_currency_code',
                    'global_currency_code' => 'sales_invoice.global_currency_code',
                    'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                        .',ifnull(sales_billing_address.lastname, \'\')))',
                    'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                        .',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region, '
                        .'\'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
                    'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                        .',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region, '
                        .'\'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
                    'shipping_information' => 'sales_order.shipping_description',
                    'subtotal' => 'sales_order.base_subtotal',
                    'shipping_and_handling' => 'sales_order.base_shipping_amount',
                    'grand_total' => 'sales_invoice.grand_total',
                    'created_at' => 'sales_invoice.created_at',
                    'updated_at' => 'sales_invoice.updated_at',
                ]
            ], 'getSelectSalesShipmentGrid' => [
                'source' => 'sales_flat_shipment_grid',
                'destination' => 'sales_shipment_grid',
                'columns' => [
                    'entity_id' => 'sales_shipment.entity_id',
                    'increment_id' => 'sales_shipment.increment_id',
                    'store_id' => 'sales_shipment.store_id',
                    'order_increment_id' => 'sales_order.increment_id',
                    'order_created_at' => 'sales_order.created_at',
                    'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                        .',ifnull(sales_order.customer_lastname, \'\')))',
                    'total_qty' => 'sales_shipment.total_qty',
                    'shipment_status' => 'sales_shipment.shipment_status',
                    'order_status' => 'sales_order.status',
                    'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                        .',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region,'
                        .' \'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
                    'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                        .',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region,'
                        .' \'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
                    'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                        .',ifnull(sales_billing_address.lastname, \'\')))',
                    'shipping_name' => 'trim(concat(ifnull(sales_shipping_address.firstname, \'\'), \' \' '
                        .',ifnull(sales_shipping_address.lastname, \'\')))',
                    'customer_email' => 'sales_order.customer_email',
                    'customer_group_id' => 'sales_order.customer_group_id',
                    'payment_method' => '(SELECT `sales_order_payment`.`method` FROM `sales_flat_order_payment` '
                        .'as sales_order_payment WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
                    'created_at' => 'sales_shipment.created_at',
                    'updated_at' => 'sales_shipment.updated_at',
                    'order_id' => 'sales_shipment.order_id',
                    'shipping_information' => 'sales_order.shipping_description'
                ]
            ], 'getSelectSalesCreditmemoGrid' => [
                'source' => 'sales_flat_creditmemo_grid',
                'destination' => 'sales_creditmemo_grid',
                'columns' => [
                    'entity_id' => 'sales_creditmemo.entity_id',
                    'increment_id' => 'sales_creditmemo.increment_id',
                    'created_at' => 'sales_creditmemo.created_at',
                    'updated_at' => 'sales_creditmemo.updated_at',
                    'order_id' => 'sales_order.entity_id',
                    'order_increment_id' => 'sales_order.increment_id',
                    'order_created_at' => 'sales_order.created_at',
                    'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                        .',ifnull(sales_billing_address.lastname, \'\')))',
                    'state' => 'sales_creditmemo.state',
                    'base_grand_total' => 'sales_creditmemo.base_grand_total',
                    'order_status' => 'sales_order.status',
                    'store_id' => 'sales_creditmemo.store_id',
                    'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                        .',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region,'
                        .' \'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
                    'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                        .',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region,'
                        .' \'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
                    'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                        .',ifnull(sales_order.customer_lastname, \'\')))',
                    'customer_email' => 'sales_order.customer_email',
                    'customer_group_id' => 'sales_order.customer_group_id',
                    'payment_method' => '(SELECT `sales_order_payment`.`method` FROM `sales_flat_order_payment` '
                        .'as sales_order_payment WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
                    'shipping_information' => 'sales_order.shipping_description',
                    'subtotal' => 'sales_creditmemo.subtotal',
                    'shipping_and_handling' => 'sales_creditmemo.shipping_amount',
                    'adjustment_positive' => 'sales_creditmemo.adjustment_positive',
                    'adjustment_negative' => 'sales_creditmemo.adjustment_negative',
                    'order_base_grand_total' => 'sales_order.base_grand_total',
                ]
            ]
        ];
    }
}
