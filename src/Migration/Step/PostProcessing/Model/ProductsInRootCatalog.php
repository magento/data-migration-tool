<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Model;

use Migration\ResourceModel;

/**
 * Class ProductsInRootCatalog
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
    const TREE_ROOT_ID = 1;

    /**
     * @param ResourceModel\Destination $destination
     */
    public function __construct(
        ResourceModel\Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
     * Returns product ids assigned to root category
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
                ['ccp' => $this->getCatalogCategoryProductDocument()],
                ['entity_id']
            )->where(
                'ccp.category_id = ?',
                self::TREE_ROOT_ID
            );
        $this->productIds = $adapter->getSelect()->getAdapter()->fetchCol($select);
        return $this->productIds;
    }

    /**
     * @return string
     */
    public function getCatalogCategoryProductDocument()
    {
        return $this->destination->addDocumentPrefix($this->catalogCategoryProductDocument);
    }
}