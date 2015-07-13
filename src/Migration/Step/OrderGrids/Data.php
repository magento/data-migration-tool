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
        foreach ($this->getDocumentList() as $document) {
            $sourceDocumentName = $document['source'];
            $destinationDocumentName = $document['destination'];
            $methodToExecute = $document['method'];
            $destinationDocument = $this->destination->getDocument($destinationDocumentName);
            $this->destination->clearDocument($destinationDocumentName);
            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocumentName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocumentName), LogManager::LOG_LEVEL_DEBUG);
            /** @var \Magento\Framework\DB\Select $select */
            $select = call_user_func_array([$this, $methodToExecute], [$sourceDocumentName]);
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

    protected function getRecords(\Magento\Framework\DB\Select $select, $pageNumber)
    {
        $select->limit($this->source->getPageSize(), $pageNumber * $this->source->getPageSize());
        return $this->sourceAdapter->loadDataFromSelect($select);
    }

    protected function getSelectSalesOrderGrid($sourceDocument)
    {
        $orderCondition = 'sfo.entity_id = ' . $sourceDocument . '.entity_id';
        $orderFields = 'sfo.shipping_description as shipping_information,'
            . 'sfo.customer_email,'
            . 'sfo.customer_group_id as customer_group,'
            . 'sfo.base_subtotal as subtotal,'
            . 'sfo.base_shipping_amount as shipping_and_handling,'
            . "trim(concat(ifnull(sfo.customer_firstname, ''), ' ' ,ifnull(sfo.customer_lastname, '')))"
            . ' as customer_name,'
            . 'sfo.total_refunded as total_refunded';
        $billingAddressCondition = 'sfoa1.parent_id = sfo.entity_id AND sfoa1.entity_id = sfo.billing_address_id';
        $billingAddressFields = "trim(concat(ifnull(sfoa1.street, ''), ', ' ,ifnull(sfoa1.city, ''),"
            . " ', ' ,ifnull(sfoa1.region, ''), ', ' ,ifnull(sfoa1.postcode, ''))) as billing_address";
        $shippingAddressCondition = 'sfoa2.parent_id = sfo.entity_id AND sfoa2.entity_id = sfo.shipping_address_id';
        $shippingAddressFields = "trim(concat(ifnull(sfoa2.street, ''), ', ' ,ifnull(sfoa2.city, ''), "
            . " ', ' ,ifnull(sfoa2.region, ''), ', ' ,ifnull(sfoa2.postcode, ''))) as shipping_address";
        $paymentMethodCondition = 'sfop.parent_id = sfo.entity_id';
        $paymentMethodFields = 'sfop.method as payment_method';

        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from($sourceDocument)
            ->joinLeft(['sfo' => 'sales_flat_order'],
                $orderCondition,
                $orderFields)
            ->joinLeft(['sfoa1' => 'sales_flat_order_address'],
                $billingAddressCondition,
                $billingAddressFields)
            ->joinLeft(['sfoa2' => 'sales_flat_order_address'],
                $shippingAddressCondition,
                $shippingAddressFields)
            ->joinLeft(['sfop' => 'sales_flat_order_payment'],
                $paymentMethodCondition,
                $paymentMethodFields);

        return $select;
    }

    protected function getSelectSalesInvoiceGrid($sourceDocument)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();

        return $select;
    }

    protected function getSelectSalesShipmentGrid($sourceDocument)
    {
        $orderCondition = 'sfo.entity_id = ' . $sourceDocument . '.entity_id';
        $orderFields = 'sfo.shipping_description as shipping_information,'
            . 'sfo.customer_email,'
            . 'sfo.customer_group_id as customer_group,'
            . 'sfo.base_subtotal as subtotal,'
            . 'sfo.base_shipping_amount as shipping_and_handling,'
            . "trim(concat(ifnull(sfo.customer_firstname, ''), ' ' ,ifnull(sfo.customer_lastname, '')))"
            . ' as customer_name,'
            . 'sfo.total_refunded as total_refunded';
        $billingAddressCondition = 'sfoa1.parent_id = sfo.entity_id AND sfoa1.entity_id = sfo.billing_address_id';
        $billingAddressFields = "trim(concat(ifnull(sfoa1.street, ''), ', ' ,ifnull(sfoa1.city, ''),"
            . " ', ' ,ifnull(sfoa1.region, ''), ', ' ,ifnull(sfoa1.postcode, ''))) as billing_address";
        $shippingAddressCondition = 'sfoa2.parent_id = sfo.entity_id AND sfoa2.entity_id = sfo.shipping_address_id';
        $shippingAddressFields = "trim(concat(ifnull(sfoa2.street, ''), ', ' ,ifnull(sfoa2.city, ''), "
            . " ', ' ,ifnull(sfoa2.region, ''), ', ' ,ifnull(sfoa2.postcode, ''))) as shipping_address";

        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from($sourceDocument)
            ->joinLeft(['sfo' => 'sales_flat_order'],
                $orderCondition,
                $orderFields)
            ->joinLeft(['sfoa1' => 'sales_flat_order_address'],
                $billingAddressCondition,
                $billingAddressFields)
            ->joinLeft(['sfoa2' => 'sales_flat_order_address'],
                $shippingAddressCondition,
                $shippingAddressFields);

        return $select;
    }

    protected function getSelectSalesCreditmemoGrid($sourceDocument)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();

        return $select;
    }

    protected function getDocumentList()
    {
        return [
            [
                'source' => 'sales_flat_order_grid',
                'destination' => 'sales_order_grid',
                'method' => 'getSelectSalesOrderGrid'
//            ], [
//                'source' => 'sales_flat_invoice_grid',
//                'destination' => 'sales_invoice_grid',
//                'method' => 'getSelectSalesInvoiceGrid'
//            ], [
//                'source' => 'sales_flat_shipment_grid',
//                'destination' => 'sales_shipment_grid',
//                'method' => 'getSelectSalesShipmentGrid'
//            ], [
//                'source' => 'sales_flat_creditmemo_grid',
//                'destination' => 'sales_creditmemo_grid',
//                'method' => 'getSelectSalesCreditmemoGrid'
            ]
        ];
    }
}
