<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Model;

use Migration\ResourceModel;

/**
 * Class can return products assigned to tree root category
 */
class ProductsInRootCatalog
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var array
     */
    private $productIds = null;

    /**
     * @var string
     */
    private $catalogCategoryProductDocument = 'catalog_category_product';

    /**
     * Id of category tree root
     */
    private $treeRootId = 1;

    /**
     * @param ResourceModel\Destination $destination
     */
    public function __construct(
        ResourceModel\Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
     * Returns product ids assigned to tree root category
     *
     * @return array
     */
    public function getProductIds()
    {
        if ($this->productIds != null) {
            return $this->productIds;
        }
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $select = $adapter->getSelect()
            ->from(
                ['ccp' => $this->destination->addDocumentPrefix($this->getCatalogCategoryProductDocument())],
                ['entity_id']
            )->where(
                'ccp.category_id = ?',
                $this->treeRootId
            );
        $this->productIds = $adapter->getSelect()->getAdapter()->fetchCol($select);
        return $this->productIds;
    }

    /**
     * Returns name of category product table
     *
     * @return string
     */
    public function getCatalogCategoryProductDocument()
    {
        return $this->catalogCategoryProductDocument;
    }
}
