<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerceInterface;

/**
 * Interface CmsPageRewrites
 */
interface CmsPageRewritesInterface
{
    /**
     * Fulfill temporary table with Cms Page url rewrites
     *
     * @return void
     */
    public function collectRewrites();
}
