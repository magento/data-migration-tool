<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11410to2000;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Step\UrlRewrite\Model\Suffix;
use Migration\Step\UrlRewrite\Model\TemporaryTableName;

/**
 * Class CategoryRewrites
 */
class CategoryRewrites
{
    /**
     * @var TemporaryTableName
     */
    private $temporaryTableName;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var AdapterMysql
     */
    private $sourceAdapter;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @param Source $source
     * @param Suffix $suffix
     * @param TemporaryTableName $temporaryTableName
     */
    public function __construct(
        Source $source,
        Suffix $suffix,
        TemporaryTableName $temporaryTableName
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->suffix = $suffix;
        $this->temporaryTableName = $temporaryTableName;
    }

    /**
     * Fulfill temporary table with category url rewrites
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
                'request_path' => sprintf("CONCAT(`r`.`request_path`, %s)", $this->suffix->getSuffix('category', 'r')),
                'target_path' => 'r.target_path',
                'is_system' => 'r.is_system',
                'store_id' => 'r.store_id',
                'entity_type' => "trim('category')",
                'redirect_type' => "trim('0')",
                'product_id' => "trim('0')",
                'category_id' => "c.entity_id",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('3')",
                'processed' => "trim('0')"
            ]
        );
        $select->join(
            ['c' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'r.value_id = c.value_id',
            []
        );
        if (!empty($urlRewriteIds)) {
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
        }
        $query = $select->where('`r`.`entity_type` = 2')
            ->insertFromSelect($this->source->addDocumentPrefix($this->temporaryTableName->getName()));
        $select->getAdapter()->query($query);
    }
}
