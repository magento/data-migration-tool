<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerce;

/**
 * Interface CategoryRewrites
 */
interface CategoryRewritesInterface
{
    /**
     * Fulfill temporary table with category url rewrites
     *
     * @param array $urlRewriteIds
     * @return void
     */
    public function collectRewrites(array $urlRewriteIds = []);
}
