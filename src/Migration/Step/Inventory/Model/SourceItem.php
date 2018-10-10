<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Migration\ResourceModel\Destination;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Class SourceItem
 */
class SourceItem
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
    private $defaultSourceCode = 'default';

    /**
     * @var string
     */
    private $sourceItemTable = 'inventory_source_item';

    /**
     * @var array
     */
    private $sourceItemTableFields = [
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
        $selectForInsert = $adapter->getSelect()
            ->from(
                ['legacy_stock_item' => $this->destination->addDocumentPrefix($this->legacyStockItemTable)],
                [
                    'source_code' => new \Zend_Db_Expr('\'' . $this->defaultSourceCode . '\''),
                    'qty',
                    'is_in_stock'
                ]
            )
            ->join(
                ['product' => $this->destination->addDocumentPrefix($this->productTable)],
                'product.entity_id = legacy_stock_item.product_id',
                'sku'
            )
            ->where('website_id = ?', 0);
        $adapter->insertFromSelect(
            $selectForInsert,
            $this->getSourceItemTable(),
            $this->getSourceItemTableFields(),
            Mysql::INSERT_ON_DUPLICATE
        );
    }

    /**
     * @return string
     */
    public function getSourceItemTable()
    {
        return $this->destination->addDocumentPrefix($this->sourceItemTable);
    }

    /**
     * @return array
     */
    public function getSourceItemTableFields()
    {
        return $this->sourceItemTableFields;
    }
}
