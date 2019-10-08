<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerce;

use Magento\Framework\Db\Select as DbSelect;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use \Migration\Step\UrlRewrite\Model\Suffix;
use \Migration\Step\UrlRewrite\Model\TemporaryTable;

/**
 * Interface ProductRewritesWithoutCategories is for product url rewrites without nested categories
 *
 * It can return SQL query ready to insert into temporary table for url rewrites
 */
interface ProductRewritesWithoutCategoriesInterface
{
    /**
     * Return query for retrieving product url rewrites when a product is saved for default scope
     *
     * @param array $urlRewriteIds
     * @return array
     */
    public function getQueryProductsSavedForDefaultScope(array $urlRewriteIds = []);

    /**
     * Return query for retrieving product url rewrites when a product is saved for particular store view
     *
     * @param array $urlRewriteIds
     * @return array
     */
    public function getQueryProductsSavedForParticularStoreView(array $urlRewriteIds = []);
}
