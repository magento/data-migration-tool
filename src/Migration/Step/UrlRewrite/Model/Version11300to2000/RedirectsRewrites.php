<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11300to2000;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TableName;
use Migration\Step\UrlRewrite\Model\VersionCommerce\RedirectsRewritesInterface;

/**
 * Class RedirectsRewrites
 */
class RedirectsRewrites implements RedirectsRewritesInterface
{
    /**
     * @var TableName
     */
    private $tableName;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var AdapterMysql
     */
    private $sourceAdapter;

    /**
     * @param Source $source
     * @param TableName $tableName
     */
    public function __construct(
        Source $source,
        TableName $tableName
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->tableName = $tableName;
    }


    /**
     * Fulfill temporary table with redirects
     *
     * @param array $urlRewriteIds
     * @return void
     */
    public function collectRewrites(array $urlRewriteIds = [])
    {
        $select = $this->sourceAdapter->getSelect();
        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' =>'r.url_rewrite_id',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => 'r.request_path',
                'target_path' => 'r.target_path',
                'is_system' => 'r.is_system',
                'store_id' => "s.store_id",
                'entity_type' => "trim('custom')",
                'redirect_type' => "(SELECT CASE eurr.options WHEN 'RP' THEN 301 WHEN 'R' THEN 302 ELSE 0 END)",
                'product_id' => "trim('0')",
                'category_id' => "trim('0')",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('2')"
            ]
        );
        $select->join(
            ['eurrr' => $this->source->addDocumentPrefix('enterprise_url_rewrite_redirect_rewrite')],
            'eurrr.url_rewrite_id = r.url_rewrite_id',
            []
        );
        $select->join(
            ['eurr' => $this->source->addDocumentPrefix('enterprise_url_rewrite_redirect')],
            'eurrr.redirect_id = eurr.redirect_id',
            []
        );
        $select->join(
            ['s' => $this->source->addDocumentPrefix('core_store')],
            's.store_id > 0',
            []
        );
        if (!empty($urlRewriteIds)) {
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
        }
        $query = $select
            ->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        $select->getAdapter()->query($query);
    }

    /**
     * Fulfill temporary table with redirects
     *
     * @param array $redirectIds
     * @return void
     */
    public function collectRedirects(array $redirectIds = [])
    {
        return;
    }

    /**
     * Remove duplicated url redirects
     *
     * @return array
     */
    public function removeDuplicatedUrlRedirects()
    {
        return[];
    }
}
