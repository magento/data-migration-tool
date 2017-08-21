<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11300to2000;

use Magento\Framework\Db\Select as DbSelect;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql as AdapterMysql;
use \Migration\Step\UrlRewrite\Model\Suffix;
use \Migration\Step\UrlRewrite\Model\TemporaryTable;

/**
 * Class ProductRewritesIncludedIntoCategories is for product url rewrites included into categories
 * It can return SQL query ready to insert into temporary table for url rewrites
 */
class ProductRewritesIncludedIntoCategories
{
    /**
     * @var TemporaryTable
     */
    private $temporaryTable;

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
     * @param Suffix $suffix
     * @param TemporaryTable $temporaryTable
     */
    public function __construct(
        Source $source,
        Suffix $suffix,
        TemporaryTable $temporaryTable
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->suffix = $suffix;
        $this->temporaryTable = $temporaryTable;
    }

    /**
     * Return query for retrieving product url rewrites
     * when a product is saved for default scope
     *
     * @return string
     */
    public function getQueryProductsSavedForDefaultScope()
    {
        $select = $this->sourceAdapter->getSelect();
        $storeSubSelect = $this->getStoreSubSelect();
        $categoriesSubSelect = $this->getCategoriesSubSelect();
        $subConcatCategories = $select->getAdapter()->getConcatSql([
            "($categoriesSubSelect)",
            "'/'",
            '`r`.`request_path`',
            $this->suffix->getSuffix('product')
        ]);
        $targetPath = 'IF(ISNULL(c.category_id), r.target_path, CONCAT(r.target_path, "/category/", c.category_id))';
        $select = $this->sourceAdapter->getSelect();
        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'request_path' => $subConcatCategories,
                'target_path' => $targetPath,
                'is_system' => 'r.is_system',
                'store_id' => 's.store_id',
                'entity_type' => "trim('product')",
                'redirect_type' => "trim('0')",
                'product_id' => "p.entity_id",
                'category_id' => "c.category_id",
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
            ['c' => $this->source->addDocumentPrefix('catalog_category_product')],
            'p.entity_id = c.product_id',
            []
        );
        $select->join(
            ['cu' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'cu.entity_id = c.category_id',
            []
        );
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
        $select->where('`ecpr`.`store_id` = 0');

        $query = $this->sourceAdapter->getSelect()->from(['result' => new \Zend_Db_Expr("($select)")])
            ->where('result.request_path IS NOT NULL')
            ->insertFromSelect($this->source->addDocumentPrefix($this->temporaryTable->getName()));
        return $query;
    }

    /**
     * Return query for retrieving product url rewrites
     * when a product is saved for particular store view
     *
     * @return string
     */
    public function getQueryProductsSavedForParticularStoreView()
    {
        $select = $this->sourceAdapter->getSelect();
        $categoriesSubSelect = $this->getCategoriesSubSelect();
        $subConcatCategories = $select->getAdapter()->getConcatSql([
            "($categoriesSubSelect)",
            "'/'",
            '`s`.`request_path`',
            $this->suffix->getSuffix('product', 'ecpr')
        ]);
        $select->from(
            ['s' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'request_path' => $subConcatCategories,
                'target_path' => 's.target_path',
                'is_system' => 's.is_system',
                'store_id' => 'ecpr.store_id',
                'entity_type' => "trim('product')",
                'redirect_type' => "trim('0')",
                'product_id' => "p.entity_id",
                'category_id' => "c.category_id",
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
            's.value_id = p.value_id and `p`.`store_id` = ecpr.store_id',
            []
        );
        $select->join(
            ['c' => $this->source->addDocumentPrefix('catalog_category_product')],
            'p.entity_id = c.product_id',
            []
        );
        $select->join(
            ['cu' => $this->source->addDocumentPrefix('catalog_category_entity_url_key')],
            'cu.entity_id = c.category_id and cu.store_id = ecpr.store_id',
            []
        );
        $query = $select
            ->where('`ecpr`.`store_id` > 0')
            ->insertFromSelect($this->source->addDocumentPrefix($this->temporaryTable->getName()));
        return $query;
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

    /**
     * Return sub-select for categories url rewrite
     *
     * @return DbSelect
     */
    private function getCategoriesSubSelect()
    {
        $subSelect = $this->sourceAdapter->getSelect();
        $subSelect->from(
            ['cr' => $this->source->addDocumentPrefix('enterprise_url_rewrite')],
            ['request_path' => 'cr.request_path']
        );
        $subSelect->join(
            ['ccr' => $this->source->addDocumentPrefix('enterprise_catalog_category_rewrite')],
            'ccr.url_rewrite_id = cr.url_rewrite_id',
            []
        );
        $subSelect->where('`cr`.`value_id` = `cu`.`value_id`');
        $subSelect->where('`ccr`.`store_id` = ecpr.`store_id`');
        return $subSelect;
    }
}
