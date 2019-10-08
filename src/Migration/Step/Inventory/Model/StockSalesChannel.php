<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Migration\ResourceModel\Destination;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class StockSalesChannel
 */
class StockSalesChannel implements TableInterface, InventoryModelInterface
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
        'type',
        'code',
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
     * @inheritdoc
     */
    public function prepareSelect()
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destination->getAdapter()->getSelect()
            ->from(
                $this->getSourceTableName(),
                ['type' => new \Zend_Db_Expr('"website"'), 'code', 'stock_id' => new \Zend_Db_Expr('"1"')]
            )
            ->where('code != ?', 'admin');
        return $select;
    }

    /**
     * @inheritdoc
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select)
    {
        $this->destination->clearDocument($this->getDestinationTableName());
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $adapter->insertFromSelect(
            $select,
            $this->getDestinationTableName(),
            $this->getDestinationTableFields(),
            Mysql::INSERT_ON_DUPLICATE
        );
    }

    /**
     * @inheritdoc
     */
    public function getDestinationTableName()
    {
        return $this->destination->addDocumentPrefix($this->stockSalesChannelTable);
    }

    /**
     * @inheritdoc
     */
    public function getDestinationTableFields()
    {
        return $this->stockSalesChannelTableFields;
    }

    /**
     * @inheritdoc
     */
    public function getSourceTableName()
    {
        return $this->destination->addDocumentPrefix($this->storeWebsiteTable);
    }
}
