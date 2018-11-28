<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\ResourceModel;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @param ResourceModel\Destination $destination
     */
    public function __construct(ResourceModel\Destination $destination)
    {
        $this->destination = $destination;
    }

    /**
     * Get select data
     *
     * @return array
     */
    public function getSelectData()
    {
        return [
            'getSelectSalesOrderGrid' => $this->getDocumentData('sales_order_grid'),
            'getSelectSalesInvoiceGrid' => $this->getDocumentData('sales_invoice_grid'),
            'getSelectSalesShipmentGrid' => $this->getDocumentData('sales_shipment_grid'),
            'getSelectSalesCreditmemoGrid'=>
                $this->getDocumentData('sales_creditmemo_grid')
        ];
    }

    /**
     * Get document data
     *
     * @param string $destinationDocument
     * @return array
     */
    protected function getDocumentData($destinationDocument)
    {
        return [
            'destination' => $destinationDocument,
            'columns' => $this->getColumnsData($destinationDocument)
        ];
    }

    /**
     * Get columns data
     *
     * @param string $gridName
     * @return array|null
     */
    protected function getColumnsData($gridName)
    {
        switch ($gridName) {
            case 'sales_order_grid':
                return $this->getSalesOrderColumnsGrid();
            case 'sales_invoice_grid':
                return $this->getSalesInvoiceColumnsGrid();
            case 'sales_shipment_grid':
                return $this->getSalesShipmentColumnsGrid();
            case 'sales_creditmemo_grid':
                return $this->getSalesCreditMemoColumnsGrid();
            default:
                return null;
        }
    }

    /**
     * Get sales order columns grid
     *
     * @return array
     */
    protected function getSalesOrderColumnsGrid()
    {
        $paymentSelect = sprintf(
            '(SELECT `sales_order_payment`.`method`
            FROM `%s` as sales_order_payment
            WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
            $this->destination->addDocumentPrefix('sales_order_payment')
        );
        $fields = array_keys($this->destination->getStructure('sales_order_grid')->getFields());
        $result = [];
        $fields = array_fill_keys($fields, null);
        $columns = [
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
                . ',ifnull(sales_shipping_address.lastname, \'\')))',
            'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                . ',ifnull(sales_billing_address.lastname, \'\')))',
            'created_at' => 'sales_order.created_at',
            'updated_at' => 'sales_order.updated_at',
            'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                . ',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region,'
                . ' \'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
            'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                . ',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region,'
                . ' \'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
            'shipping_information' => 'sales_order.shipping_description',
            'customer_email' => 'sales_order.customer_email',
            'customer_group' => 'sales_order.customer_group_id',
            'subtotal' => 'sales_order.base_subtotal',
            'shipping_and_handling' => 'sales_order.base_shipping_amount',
            'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                . ',ifnull(sales_order.customer_lastname, \'\')))',
            'payment_method' => $paymentSelect,
            'total_refunded' => 'sales_order.total_refunded'
        ];
        foreach (array_keys($fields) as $key) {
            $result[$key] = isset($columns[$key]) ? $columns[$key] : 'null';
        }

        return $result;
    }

    /**
     * Get sales invoice columns grid
     *
     * @return array
     */
    protected function getSalesInvoiceColumnsGrid()
    {
        $paymentSelect = sprintf(
            '(SELECT `sales_order_payment`.`method`
            FROM `%s` as sales_order_payment
            WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
            $this->destination->addDocumentPrefix('sales_order_payment')
        );
        $fields = array_keys($this->destination->getStructure('sales_invoice_grid')->getFields());
        $result = [];
        $fields = array_fill_keys($fields, null);
        $columns = [
            'entity_id' => 'sales_invoice.entity_id',
            'increment_id' => 'sales_invoice.increment_id',
            'state' => 'sales_invoice.state',
            'store_id' => 'sales_invoice.store_id',
            'store_name' => 'sales_order.store_name',
            'order_id' => 'sales_invoice.order_id',
            'order_increment_id' => 'sales_order.increment_id',
            'order_created_at' => 'sales_order.created_at',
            'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                . ',ifnull(sales_order.customer_lastname, \'\')))',
            'customer_email' => 'sales_order.customer_email',
            'customer_group_id' => 'sales_order.customer_group_id',
            'payment_method' => $paymentSelect,
            'store_currency_code' => 'sales_invoice.store_currency_code',
            'order_currency_code' => 'sales_invoice.order_currency_code',
            'base_currency_code' => 'sales_invoice.base_currency_code',
            'global_currency_code' => 'sales_invoice.global_currency_code',
            'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                . ',ifnull(sales_billing_address.lastname, \'\')))',
            'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                . ',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region, '
                . '\'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
            'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                . ',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region, '
                . '\'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
            'shipping_information' => 'sales_order.shipping_description',
            'subtotal' => 'sales_order.base_subtotal',
            'shipping_and_handling' => 'sales_order.base_shipping_amount',
            'grand_total' => 'sales_invoice.grand_total',
            'base_grand_total' => 'sales_invoice.base_grand_total',
            'created_at' => 'sales_invoice.created_at',
            'updated_at' => 'sales_invoice.updated_at'
        ];
        foreach (array_keys($fields) as $key) {
            $result[$key] = isset($columns[$key]) ? $columns[$key] : 'null';
        }
        return $result;
    }

    /**
     * Get sales shipment columns grid
     *
     * @return array
     */
    protected function getSalesShipmentColumnsGrid()
    {
        $paymentSelect = sprintf(
            '(SELECT `sales_order_payment`.`method`
            FROM `%s` as sales_order_payment
            WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
            $this->destination->addDocumentPrefix('sales_order_payment')
        );
        $fields = array_keys($this->destination->getStructure('sales_shipment_grid')->getFields());
        $result = [];
        $fields = array_fill_keys($fields, null);
        $columns = [
            'entity_id' => 'sales_shipment.entity_id',
            'increment_id' => 'sales_shipment.increment_id',
            'store_id' => 'sales_shipment.store_id',
            'order_increment_id' => 'sales_order.increment_id',
            'order_id' => 'sales_shipment.order_id',
            'order_created_at' => 'sales_order.created_at',
            'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                . ',ifnull(sales_order.customer_lastname, \'\')))',
            'total_qty' => 'sales_shipment.total_qty',
            'shipment_status' => 'sales_shipment.shipment_status',
            'order_status' => 'sales_order.status',
            'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                . ',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region,'
                . ' \'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
            'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                . ',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region,'
                . ' \'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
            'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                . ',ifnull(sales_billing_address.lastname, \'\')))',
            'shipping_name' => 'trim(concat(ifnull(sales_shipping_address.firstname, \'\'), \' \' '
                . ',ifnull(sales_shipping_address.lastname, \'\')))',
            'customer_email' => 'sales_order.customer_email',
            'customer_group_id' => 'sales_order.customer_group_id',
            'payment_method' => $paymentSelect,
                'shipping_information' => 'sales_order.shipping_description',
            'created_at' => 'sales_shipment.created_at',
            'updated_at' => 'sales_shipment.updated_at'
        ];
        foreach (array_keys($fields) as $key) {
            $result[$key] = isset($columns[$key]) ? $columns[$key] : 'null';
        }
        return $result;
    }

    /**
     * Get sales credit memo columns grid
     *
     * @return array
     */
    protected function getSalesCreditMemoColumnsGrid()
    {
        $paymentSelect = sprintf(
            '(SELECT `sales_order_payment`.`method`
            FROM `%s` as sales_order_payment
            WHERE (`parent_id` = sales_order.entity_id) LIMIT 1)',
            $this->destination->addDocumentPrefix('sales_order_payment')
        );
        $fields = array_keys($this->destination->getStructure('sales_creditmemo_grid')->getFields());
        $result = [];
        $fields = array_fill_keys($fields, null);
        $columns = [
            'entity_id' => 'sales_creditmemo.entity_id',
            'increment_id' => 'sales_creditmemo.increment_id',
            'created_at' => 'sales_creditmemo.created_at',
            'updated_at' => 'sales_creditmemo.updated_at',
            'order_id' => 'sales_order.entity_id',
            'order_increment_id' => 'sales_order.increment_id',
            'order_created_at' => 'sales_order.created_at',
            'billing_name' => 'trim(concat(ifnull(sales_billing_address.firstname, \'\'), \' \' '
                . ',ifnull(sales_billing_address.lastname, \'\')))',
            'state' => 'sales_creditmemo.state',
            'base_grand_total' => 'sales_creditmemo.base_grand_total',
            'order_status' => 'sales_order.status',
            'store_id' => 'sales_creditmemo.store_id',
            'billing_address' => 'trim(concat(ifnull(sales_billing_address.street, \'\'), \', \' '
                . ',ifnull(sales_billing_address.city, \'\'), \', \' ,ifnull(sales_billing_address.region,'
                . ' \'\'), \', \' ,ifnull(sales_billing_address.postcode, \'\')))',
            'shipping_address' => 'trim(concat(ifnull(sales_shipping_address.street, \'\'), \', \' '
                . ',ifnull(sales_shipping_address.city, \'\'), \', \' ,ifnull(sales_shipping_address.region,'
                . ' \'\'), \', \' ,ifnull(sales_shipping_address.postcode, \'\')))',
            'customer_name' => 'trim(concat(ifnull(sales_order.customer_firstname, \'\'), \' \' '
                . ',ifnull(sales_order.customer_lastname, \'\')))',
            'customer_email' => 'sales_order.customer_email',
            'customer_group_id' => 'sales_order.customer_group_id',
            'payment_method' => $paymentSelect,
            'shipping_information' => 'sales_order.shipping_description',
            'subtotal' => 'sales_creditmemo.subtotal',
            'shipping_and_handling' => 'sales_creditmemo.shipping_amount',
            'adjustment_positive' => 'sales_creditmemo.adjustment_positive',
            'adjustment_negative' => 'sales_creditmemo.adjustment_negative',
            'order_base_grand_total' => 'sales_order.base_grand_total'
        ];
        foreach (array_keys($fields) as $key) {
            $result[$key] = isset($columns[$key]) ? $columns[$key] : 'null';
        }
        return $result;
    }

    /**
     * Get document list
     *
     * @return array
     */
    public function getDocumentList()
    {
        return [
            'sales_flat_order_grid' => 'sales_order_grid',
            'sales_flat_invoice_grid' => 'sales_invoice_grid',
            'sales_flat_shipment_grid' => 'sales_shipment_grid',
            'sales_flat_creditmemo_grid' => 'sales_creditmemo_grid'
        ];
    }

    /**
     * Get document columns
     *
     * @param string $documentName
     * @return array
     */
    public function getDocumentColumns($documentName)
    {
        $columnsData = $this->getColumnsData($documentName);
        return array_keys($columnsData);
    }

    /**
     * Get update data
     *
     * @return array
     */
    public function getUpdateData()
    {
        return [
            'sales_flat_order' => [
                'idKey' => 'entity_id',
                'methods' => [
                    'getSelectSalesOrderGrid',
                    'getSelectSalesInvoiceGrid',
                    'getSelectSalesShipmentGrid',
                    'getSelectSalesCreditmemoGrid'
                ]
            ],
            'sales_flat_invoice' => [
                'idKey' => 'entity_id',
                'methods' => [
                    'getSelectSalesInvoiceGrid',
                ]
            ],
            'sales_flat_shipment' => [
                'idKey' => 'entity_id',
                'methods' => [
                    'getSelectSalesShipmentGrid',
                ]
            ],
            'sales_flat_creditmemo' => [
                'idKey' => 'entity_id',
                'methods' => [
                    'getSelectSalesCreditmemoGrid'
                ]
            ],
            'sales_flat_order_address' => [
                'idKey' => 'parent_id',
                'methods' => [
                    'getSelectSalesOrderGrid',
                    'getSelectSalesInvoiceGrid',
                    'getSelectSalesShipmentGrid',
                    'getSelectSalesCreditmemoGrid'
                ]
            ],
            'sales_flat_order_payment' => [
                'idKey' => 'parent_id',
                'methods' => [
                    'getSelectSalesOrderGrid',
                    'getSelectSalesInvoiceGrid',
                    'getSelectSalesShipmentGrid',
                    'getSelectSalesCreditmemoGrid'
                ]
            ]
        ];
    }
}
