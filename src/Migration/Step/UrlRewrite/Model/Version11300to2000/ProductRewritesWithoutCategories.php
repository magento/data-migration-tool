<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11300to2000;

use Magento\Framework\Db\Select as DbSelect;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Step\UrlRewrite\Model\Suffix;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TableName;
use Migration\Step\UrlRewrite\Model\VersionCommerce\ProductRewritesWithoutCategoriesInterface;

/**
 * Class ProductRewritesWithoutCategories is for product url rewrites without nested categories
 *
 * It can return SQL query ready to insert into temporary table for url rewrites
 */
class ProductRewritesWithoutCategories implements ProductRewritesWithoutCategoriesInterface
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
     * @var Suffix
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
     * Return query for retrieving product url rewrites when a product is saved for default scope
     *
     * @param array $urlRewriteIds
     * @return array
     */
    public function getQueryProductsSavedForDefaultScope(array $urlRewriteIds = [])
    {
        $select = $this->sourceAdapter->getSelect();
        $storeSubSelect = $this->getStoreSubSelect();
        $subConcat = $select->getAdapter()->getConcatSql([
            '`r`.`request_path`',
            $this->suffix->getSuffix('product')
        ]);

        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' =>'r.url_rewrite_id',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => $subConcat,
                'target_path' => 'r.target_path',
                'is_system' => 'r.is_system',
                'store_id' => 's.store_id',
                'entity_type' => "trim('product')",
                'redirect_type' => "trim('0')",
                'product_id' => "p.entity_id",
                'category_id' => "trim('0')",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('4')"
            ]
        );
        $select->join(
            ['ecpr' => $this->source->addDocumentPrefix('enterprise_catalog_product_rewrite')],
            'ecpr.url_rewrite_id = r.url_rewrite_id',
            []
        );
        $select->join(
            ['p' => $this->source->addDocumentPrefix('catalog_product_entity_url_key')],
            'r.value_id = p.value_id',
            []
        );
        $select->join(
            ['cpw' => $this->source->addDocumentPrefix('catalog_product_website')],
            'p.entity_id = cpw.product_id',
            []
        );
        $select->join(
            ['s' => $this->source->addDocumentPrefix('core_store')],
            sprintf('cpw.website_id = s.website_id and s.store_id not in (%s)', $storeSubSelect),
            []
        );
        if (!empty($urlRewriteIds)) {
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
        }
        $query = $select
            ->where('`ecpr`.`store_id` = 0')
            ->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        return [$query];
    }

    /**
     * Return query for retrieving product url rewrites when a product is saved for particular store view
     *
     * @param array $urlRewriteIds
     * @return array
     */
    public function getQueryProductsSavedForParticularStoreView(array $urlRewriteIds = [])
    {
        $select = $this->sourceAdapter->getSelect();
        $subConcat = $select->getAdapter()->getConcatSql([
            '`s`.`request_path`',
            $this->suffix->getSuffix('product', 'ecpr')
        ]);
        $select->from(
            ['s' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' =>'s.url_rewrite_id',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => $subConcat,
                'target_path' => 's.target_path',
                'is_system' => 's.is_system',
                'store_id' => 'ecpr.store_id',
                'entity_type' => "trim('product')",
                'redirect_type' => "trim('0')",
                'product_id' => "p.entity_id",
                'category_id' => "trim('0')",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('4')"
            ]
        );
        $select->join(
            ['ecpr' => $this->source->addDocumentPrefix('enterprise_catalog_product_rewrite')],
            'ecpr.url_rewrite_id = s.url_rewrite_id',
            []
        );
        $select->join(
            ['p' => $this->source->addDocumentPrefix('catalog_product_entity_url_key')],
            's.value_id = p.value_id',
            []
        );
        if (!empty($urlRewriteIds)) {
            $select->where('s.url_rewrite_id in (?)', $urlRewriteIds);
        }
        $query = $select
            ->where('`ecpr`.`store_id` > 0')
            ->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        return [$query];
    }

    /**
     * Return select for product url rewrite where store above 0
     *
     * @return DbSelect
     */
    private function getStoreSubSelect()
    {
        $storeSubSelect = $this->sourceAdapter->getSelect();
        $storeSubSelect->from(
            ['sr' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            ['store_id' => 'ecpr.store_id']
        );
        $storeSubSelect->join(
            ['srcu' => $this->source->addDocumentPrefix('catalog_product_entity_url_key')],
            'srcu.value_id = sr.value_id',
            []
        );
        $storeSubSelect->join(
            ['ecpr' => $this->source->addDocumentPrefix('enterprise_catalog_product_rewrite')],
            'ecpr.url_rewrite_id = sr.url_rewrite_id',
            []
        );
        $storeSubSelect
            ->where('srcu.entity_id = p.entity_id')
            ->where('ecpr.store_id > 0');
        return $storeSubSelect;
    }
}
