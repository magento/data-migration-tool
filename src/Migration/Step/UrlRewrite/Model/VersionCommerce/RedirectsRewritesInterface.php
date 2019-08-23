<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerce;

/**
 * Interface RedirectsRewrites
 */
interface RedirectsRewritesInterface
{
    /**
     * Fulfill temporary table with redirects
     *
     * @param array $urlRewriteIds
     * @return void
     */
    public function collectRewrites(array $urlRewriteIds = []);

    /**
     * Fulfill temporary table with redirects
     *
     * @param array $redirectIds
     * @return void
     */
    public function collectRedirects(array $redirectIds = []);

    /**
     * Remove duplicated url redirects
     *
     * @return array
     */
    public function removeDuplicatedUrlRedirects();
}
