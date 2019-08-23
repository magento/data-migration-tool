<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerce;

/**
 * Interface ProductRewritesIncludedIntoCategories is for product url rewrites included into categories
 *
 * It can return SQL query ready to insert into temporary table for url rewrites
 */
interface ProductRewritesIncludedIntoCategoriesInterface
{
    /**
     * Return query for retrieving product url rewrites for stores when a product was saved for default scope
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
