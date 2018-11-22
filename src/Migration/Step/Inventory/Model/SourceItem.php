<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Migration\ResourceModel\Destination;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class SourceItem
 */
class SourceItem implements TableInterface, InventoryModelInterface
{
    /**
     * Destination resource
     *
     * @var Destination
     */
    private $destination;

    /**
     * @var InventorySource
     */
    private $inventorySource;

    /**
     * @var string
     */
    private $sourceItemTable = 'inventory_source_item';

    /**
     * @var array
     */
    private $sourceItemTableFields = [
        'source_item_id',
        'source_code',
        'quantity',
        'status',
        'sku',
    ];

    /**
     * @var string
     */
    private $legacyStockItemTable = 'cataloginventory_stock_item';

    /**
     * @var string
     */
    private $productTable = 'catalog_product_entity';

    /**
     * @param Destination $destination
     * @param InventorySource $inventorySource
     */
    public function __construct(
        Destination $destination,
        InventorySource $inventorySource
    ) {
        $this->inventorySource = $inventorySource;
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
                ['legacy_stock_item' => $this->getSourceTableName()],
                [
                    'source_code' => new \Zend_Db_Expr("'" . $this->inventorySource->getDefaultSourceCode() . "'"),
                    'quantity' => 'qty',
                    'status' => 'is_in_stock'
                ]
            )
            ->join(
                ['product' => $this->destination->addDocumentPrefix($this->productTable)],
                'product.entity_id = legacy_stock_item.product_id',
                'sku'
            )
            ->where('website_id = ?', 0);
        return $select;
    }

    /**
     * @inheritdoc
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $adapter->insertFromSelect(
            $select,
            $this->getDestinationTableName(),
            array_diff($this->getDestinationTableFields(), ['source_item_id']),
            Mysql::INSERT_ON_DUPLICATE
        );
    }

    /**
     * @inheritdoc
     */
    public function getDestinationTableName()
    {
        return $this->destination->addDocumentPrefix($this->sourceItemTable);
    }

    /**
     * @inheritdoc
     */
    public function getDestinationTableFields()
    {
        return $this->sourceItemTableFields;
    }

    /**
     * @inheritdoc
     */
    public function getSourceTableName()
    {
        return $this->destination->addDocumentPrefix($this->legacyStockItemTable);
    }
}
