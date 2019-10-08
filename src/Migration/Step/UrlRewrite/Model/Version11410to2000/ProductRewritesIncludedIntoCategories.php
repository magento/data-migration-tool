<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11410to2000;

use Magento\Framework\Db\Select as Select;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use Migration\Step\UrlRewrite\Model\Suffix;
use Migration\Step\UrlRewrite\Model\VersionCommerce\TableName;
use Migration\Step\UrlRewrite\Model\VersionCommerce\ProductRewritesIncludedIntoCategoriesInterface;

/**
 * Class ProductRewritesIncludedIntoCategories is for product url rewrites included into categories
 *
 * It can return SQL query ready to insert into temporary table for url rewrites
 */
class ProductRewritesIncludedIntoCategories implements ProductRewritesIncludedIntoCategoriesInterface
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
    private $anchorAttributeId;

    /**
     * @var string
     */
    private $anchorAttributeCode = 'is_anchor';

    /**
     * @var string
     */
    private $categoryEntityTypeCode = 'catalog_category';

    /**
     * @var array
     */
    private $anchorCategories = [];

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
     * Return query for retrieving product url rewrites for stores when a product was saved for default scope
     *
     * @param array $urlRewriteIds
     * @return array
     */
    public function getQueryProductsSavedForDefaultScope(array $urlRewriteIds = [])
    {
        $queries = [];
        $selects = [];
        $config = [
            'url_rewrite_ids' => $urlRewriteIds,
            'store_id' => 's.store_id',
            'store_main_table' => 's',
            'add_stores_to_select' => true
        ];
        $select = $this->getSelectBase($config)->where('`r`.`store_id` = 0');
        $selects[] = $this->addStoresToSelect($select);
        foreach ($this->getSelectsForAnchorCategories($config) as $select) {
            $select->where('`r`.`store_id` = 0');
            $selects[] = $select;
        }
        foreach ($selects as $select) {
            $queries[] = $this->sourceAdapter->getSelect()->from(['result' => new \Zend_Db_Expr("($select)")])
                ->where('result.request_path IS NOT NULL')
                ->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        }
        return $queries;
    }

    /**
     * Return query for retrieving product url rewrites when a product is saved for particular store view
     *
     * @param array $urlRewriteIds
     * @return array
     */
    public function getQueryProductsSavedForParticularStoreView(array $urlRewriteIds = [])
    {
        $queries = [];
        $selects = [];
        $config = ['url_rewrite_ids' => $urlRewriteIds];
        $select = $this->getSelectBase($config)->where('`r`.`store_id` > 0');
        $selects[] = $select;
        foreach ($this->getSelectsForAnchorCategories($config) as $select) {
            $select->where('`r`.`store_id` > 0');
            $selects[] = $select;
        }
        foreach ($selects as $select) {
            $queries[] = $this->sourceAdapter->getSelect()->from(['result' => new \Zend_Db_Expr("($select)")])
                ->where('result.request_path IS NOT NULL')
                ->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        }
        return $queries;
    }

    /**
     * Return base select query
     *
     * @param array $config
     * @return Select
     */
    private function getSelectBase(array $config)
    {
        $urlRewriteIds = $config['url_rewrite_ids'];
        $storeId = $config['store_id'] ?? 'r.store_id';
        $storeMainTable = $config['store_main_table'] ?? 'r';
        $targetPath = $config['target_path'] ??
            'IF(ISNULL(c.category_id), r.target_path, CONCAT(r.target_path, "/category/", c.category_id))';
        $categoriesSubSelect = $this->sourceAdapter->getSelect();
        $categoriesSubSelect->from(
            ['cr' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            ['request_path' => 'cr.request_path']
        );
        $categoriesSubSelect->where('`cr`.`value_id` = `cu`.`value_id`');
        $categoriesSubSelect->where('`cr`.`entity_type` = 2');
        $categoriesSubSelect->where('`cr`.`store_id` = ' . $storeId);
        $subConcatCategories = $categoriesSubSelect->getAdapter()->getConcatSql([
            "($categoriesSubSelect)",
            "'/'",
            '`r`.`request_path`',
            $this->suffix->getSuffix('product', $storeMainTable)
        ]);
        $select = $this->sourceAdapter->getSelect();
        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' =>'r.url_rewrite_id',
                'redirect_id' => 'IFNULL(NULL, NULL)',
                'request_path' => $config['request_path'] ?? $subConcatCategories,
                'target_path' => $targetPath,
                'is_system' => 'r.is_system',
                'store_id' => $storeId,
                'entity_type' => "trim('product')",
                'redirect_type' => "trim('0')",
                'product_id' => "p.entity_id",
                'category_id' => $config['category_id'] ?? 'c.category_id',
                'cms_page_id' => "trim('0')",
                'priority' => "trim('4')"
            ]
        );
        $select->join(
            ['p' => $this->source->addDocumentPrefix('catalog_product_entity_url_key')],
            'r.value_id = p.value_id',
            []
        );
        $select->join(
            ['c' => $this->source->addDocumentPrefix('catalog_category_product')],
            'p.entity_id = c.product_id',
            []
        );
        $select->join(
            ['cu' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'cu.entity_id = c.category_id',
            []
        );
        $select->where('`r`.`entity_type` = 3');
        if (!empty($urlRewriteIds)) {
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
        }
        return $select;
    }

    /**
     * Return select queries to fetch products assigned to sub categories of anchor categories
     *
     * @param array $config
     * @return array
     */
    private function getSelectsForAnchorCategories(array $config)
    {
        $storeMainTable = $config['store_main_table'] ?? 'r';
        $selects = [];
        foreach ($this->getAnchorCategories() as $category) {
            // make request_path field from anchor category path plus product name
            $subConcatCategories = $this->sourceAdapter->getSelect()->getAdapter()->getConcatSql([
                "'" . $category['anchor']['request_path'] . "'",
                "'/'",
                '`r`.`request_path`',
                $this->suffix->getSuffix('product', $storeMainTable)
            ]);
            $targetPath = new \Zend_Db_Expr(
                'CONCAT(r.target_path, "/category/", ' . $category['anchor']['entity_id'] . ')'
            );
            $categoryId = new \Zend_Db_Expr("trim('" . $category['anchor']['entity_id'] . "')");
            $config = array_merge($config, [
                'request_path' => $subConcatCategories,
                'target_path' => $targetPath,
                'category_id' => $categoryId,
            ]);
            $select = $this->getSelectBase($config);
            $select = !empty($config['add_stores_to_select']) ? $this->addStoresToSelect($select) : $select;
            $select->where('c.category_id IN (?)', $category['subcategories']);
            $select->group(['request_path', 'store_id']);
            $selects[] = $select;
        }
        return $selects;
    }

    /**
     * Add stores to select
     *
     * @param Select $select
     * @return Select
     */
    private function addStoresToSelect(Select $select)
    {
        $storeSubSelect = $this->sourceAdapter->getSelect();
        $storeSubSelect->from(
            ['sr' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            ['store_id' => 'sr.store_id']
        );
        $storeSubSelect->join(
            ['srcu' => $this->source->addDocumentPrefix('catalog_product_entity_url_key')],
            'srcu.value_id = sr.value_id',
            []
        );
        $storeSubSelect->where('sr.entity_type = 3')
            ->where('srcu.entity_id = p.entity_id')
            ->where('sr.store_id > 0');

        $select->join(
            ['cpw' => $this->source->addDocumentPrefix('catalog_product_website')],
            'c.product_id = cpw.product_id',
            []
        );
        $select->join(
            ['s' => $this->source->addDocumentPrefix('core_store')],
            sprintf('cpw.website_id = s.website_id and s.store_id not in (%s)', $storeSubSelect),
            []
        );
        return $select;
    }

    /**
     * Get anchor categories and its sub categories
     *
     * @return array
     */
    private function getAnchorCategories()
    {
        if (!empty($this->anchorCategories)) {
            return $this->anchorCategories;
        }
        $select = $this->sourceAdapter->getSelect();
        $select->from(
            ['ccei' => $this->source->addDocumentPrefix('catalog_category_entity_int')],
            []
        )->join(
            ['cce' => $this->source->addDocumentPrefix('catalog_category_entity')],
            'ccei.entity_id = cce.entity_id',
            ['entity_id', 'path']
        )->join(
            ['eet' => $this->source->addDocumentPrefix('eav_entity_type')],
            'eet.entity_type_id = ccei.entity_type_id',
            []
        )->join(
            ['cceuk' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'cceuk.entity_id = ccei.entity_id and cceuk.store_id = 0',
            []
        )->join(
            ['eur' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            'eur.value_id = cceuk.value_id and eur.entity_type = 2',
            ['request_path']
        )->where('ccei.attribute_id = ?', $this->getAnchorAttributeId()
        )->where('ccei.value = 1'
        )->where('eet.entity_type_code = ?', $this->categoryEntityTypeCode
        )->group('eur.value_id');

        $anchorCategories = $select->getAdapter()->fetchAll($select);
        if (!$anchorCategories) {
            return $this->anchorCategories;
        }
        $i = 0;
        foreach ($anchorCategories as $category) {
            $select = $this->sourceAdapter->getSelect();
            $select->from(['cce' => $this->source->addDocumentPrefix('catalog_category_entity')], ['entity_id']);
            $select->where('cce.path LIKE ?', $category['path'] . '/%');
            if($subCategoryIds = $select->getAdapter()->fetchCol($select)) {
                $this->anchorCategories[$i]['anchor'] = $category;
                $this->anchorCategories[$i++]['subcategories'] = $subCategoryIds;
            }
        }
        return $this->anchorCategories;
    }

    /**
     * Get anchor attribute id
     *
     * @return string
     */
    protected function getAnchorAttributeId()
    {
        if (!empty($this->anchorAttributeId)) {
            return $this->anchorAttributeId;
        }
        $select = $this->sourceAdapter->getSelect();
        $query = $select->from(
            ['ea' => $this->source->addDocumentPrefix('eav_attribute')],
            ['attribute_id']
        )->join(
            ['eet' => $this->source->addDocumentPrefix('eav_entity_type')],
            'eet.entity_type_id = ea.entity_type_id',
            []
        )->where('ea.attribute_code = ?', $this->anchorAttributeCode
        )->where('eet.entity_type_code = ?', $this->categoryEntityTypeCode);
        $this->anchorAttributeId = $query->getAdapter()->fetchOne($query);
        return $this->anchorAttributeId;
    }
}
