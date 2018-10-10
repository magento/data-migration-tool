<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Migration\ResourceModel\Destination;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class StockSalesChannel
 */
class StockSalesChannel
{
    /**
     * Destination resource
     *
     * @var Destination
     */
    private $destination;

    /**
     * @var string
     */
    private $storeWebsiteTable = 'store_website';

    /**
     * @var string
     */
    private $stockSalesChannelTable = 'inventory_stock_sales_channel';

    /**
     * @var array
     */
    private $stockSalesChannelTableFields = [
        'code',
        'type',
        'stock_id'
    ];

    /**
     * @param Destination $destination
     */
    public function __construct(
        Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
     * {@inheritdoc}
     */
    public function fill()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $selectForInsert = $adapter
            ->getSelect()
            ->from($this->destination->addDocumentPrefix($this->storeWebsiteTable), ['code'])
            ->columns(['type' => new \Zend_Db_Expr('"website"'), 'stock_id' => new \Zend_Db_Expr('"1"')])
            ->where('code != ?', 'admin');
        $adapter->insertFromSelect(
            $selectForInsert,
            $this->getStockSalesChannelTable(),
            $this->getStockSalesChannelTableFields(),
            Mysql::INSERT_ON_DUPLICATE
        );
    }

    /**
     * @return string
     */
    public function getStockSalesChannelTable()
    {
        return $this->destination->addDocumentPrefix($this->stockSalesChannelTable);
    }

    /**
     * @return array
     */
    public function getStockSalesChannelTableFields()
    {
        return $this->stockSalesChannelTableFields;
    }
}
