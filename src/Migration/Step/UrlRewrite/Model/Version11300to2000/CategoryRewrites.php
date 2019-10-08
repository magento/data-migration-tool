<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11300to2000;

use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Step\UrlRewrite\Model\Suffix;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TableName;
use Migration\Step\UrlRewrite\Model\VersionCommerce\CategoryRewritesInterface;

/**
 * Class CategoryRewrites
 */
class CategoryRewrites implements CategoryRewritesInterface
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
     * @var string
     */
    private $suffix;

    /**
     * @param Source $source
     * @param Suffix $suffix
     * @param TableName $tableName
     */
    public function __construct(
        Source $source,
        Suffix $suffix,
        TableName $tableName
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->suffix = $suffix;
        $this->tableName = $tableName;
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
        $requestPath = sprintf("CONCAT(`r`.`request_path`, %s)", $this->suffix->getSuffix('category', 'eccr'));
        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' =>'r.url_rewrite_id',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => $requestPath,
                'target_path' => 'r.target_path',
                'is_system' => 'r.is_system',
                'store_id' => 's.store_id',
                'entity_type' => "trim('category')",
                'redirect_type' => "trim('0')",
                'product_id' => "trim('0')",
                'category_id' => "c.entity_id",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('3')"
            ]
        );
        $select->join(
            ['c' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'r.value_id = c.value_id',
            []
        );
        $select->join(
            ['eccr' => $this->source->addDocumentPrefix('enterprise_catalog_category_rewrite')],
            'eccr.url_rewrite_id = r.url_rewrite_id and eccr.store_id = 0',
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

        $requestPath = sprintf("CONCAT(`r`.`request_path`, %s)", $this->suffix->getSuffix('category', 'eccr'));
        $select = $this->sourceAdapter->getSelect();
        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' =>'r.url_rewrite_id',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => $requestPath,
                'target_path' => 'r.target_path',
                'is_system' => 'r.is_system',
                'store_id' => 'eccr.store_id',
                'entity_type' => "trim('category')",
                'redirect_type' => "trim('0')",
                'product_id' => "trim('0')",
                'category_id' => "c.entity_id",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('3')"
            ]
        );
        $select->join(
            ['c' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'r.value_id = c.value_id',
            []
        );
        $select->join(
            ['eccr' => $this->source->addDocumentPrefix('enterprise_catalog_category_rewrite')],
            'eccr.url_rewrite_id = r.url_rewrite_id and eccr.store_id > 0',
            []
        );
        if (!empty($urlRewriteIds)) {
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
        }

        $query = $select->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        $select->getAdapter()->query($query);
    }
}
