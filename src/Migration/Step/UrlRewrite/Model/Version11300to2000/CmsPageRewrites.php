<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11300to2000;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TableName;
use Migration\Step\UrlRewrite\Model\VersionCommerce\CmsPageRewritesInterface;

/**
 * Class CmsPageRewrites
 */
class CmsPageRewrites implements CmsPageRewritesInterface
{
    /**
     * @var string
     */
    protected $cmsPageTableName = 'cms_page';

    /**
     * @var string
     */
    protected $cmsPageStoreTableName = 'cms_page_store';

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
     * Fulfill temporary table with Cms Page url rewrites
     *
     * @return void
     */
    public function collectRewrites()
    {
        $select = $this->sourceAdapter->getSelect();
        $select->distinct()->from(
            ['cp' => $this->source->addDocumentPrefix($this->cmsPageTableName)],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' => 'IFNULL(NULL, NULL)',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => 'cp.identifier',
                'target_path' => 'CONCAT("cms/page/view/page_id/", cp.page_id)',
                'is_system' => "trim('1')",
                'store_id' => 'IF(cps.store_id = 0, 1, cps.store_id)',
                'entity_type' => "trim('cms-page')",
                'redirect_type' => "trim('0')",
                'product_id' => "trim('0')",
                'category_id' => "trim('0')",
                'cms_page_id' => "cp.page_id",
                'priority' => "trim('5')"
            ]
        )->joinLeft(
            ['cps' => $this->source->addDocumentPrefix($this->cmsPageStoreTableName)],
            'cps.page_id = cp.page_id',
            []
        )->group(['request_path', 'cps.store_id']);
        $query = $select->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        $select->getAdapter()->query($query);
    }
}
