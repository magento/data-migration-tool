<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\Version11410to2000;

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
                'store_id' => 'r.store_id',
                'entity_type' => "trim('custom')",
                'redirect_type' => "trim('0')",
                'product_id' => "trim('0')",
                'category_id' => "trim('0')",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('2')"
            ]
        );
        if (!empty($urlRewriteIds)) {
            $select->where('r.url_rewrite_id in (?)', $urlRewriteIds);
        }
        $query = $select->where('`r`.`entity_type` = 1')
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
        $select = $this->sourceAdapter->getSelect();
        $adapter = $select->getAdapter();
        $cases = [
            'r.category_id IS NOT NULL and r.product_id IS NOT NULL'
                => $adapter->getConcatSql([
                    '"catalog/product/view/id/"',
                    'r.product_id',
                    '"/category/"',
                    'r.category_id']
                )
            ,
            'r.category_id IS NULL and r.product_id IS NOT NULL'
                => $adapter->getConcatSql(['"catalog/product/view/id/"', 'r.product_id'])
            ,
            'r.category_id IS NOT NULL and r.product_id IS NULL'
                => $adapter->getConcatSql(['"catalog/category/view/id/"', 'r.category_id'])

        ];
        $targetPath = $adapter->getCaseSql('', $cases, 'r.target_path');
        $select->from(
            ['r' => $this->source->addDocumentPrefix('enterprise_url_rewrite_redirect')],
            [
                'id' => 'IFNULL(NULL, NULL)',
                'url_rewrite_id' => 'IFNULL(NULL, NULL)',
                'redirect_id' => 'r.redirect_id',
                'request_path' => 'r.identifier',
                'target_path' => $targetPath,
                'is_system' => "trim('0')",
                'store_id' => 'r.store_id',
                'entity_type' => "trim('custom')",
                'redirect_type' => "(SELECT CASE r.options WHEN 'RP' THEN 301 WHEN 'R' THEN 302 ELSE 0 END)",
                'product_id' => "r.product_id",
                'category_id' => "r.category_id",
                'cms_page_id' => "trim('0')",
                'priority' => "trim('1')"
            ]
        );
        if (!empty($redirectIds)) {
            $select->where('r.redirect_id in (?)', $redirectIds);
        }
        $query = $select->insertFromSelect($this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()));
        $adapter->query($query);
    }

    /**
     * Remove duplicated url redirects
     *
     * @return array
     */
    public function removeDuplicatedUrlRedirects()
    {
        $select = $this->sourceAdapter->getSelect();
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $select->getAdapter();
        $select->from(['t' => $this->source->addDocumentPrefix($this->tableName->getTemporaryTableName())],['id']);
        $select->join(
            ['eurr' => $this->source->addDocumentPrefix('enterprise_url_rewrite_redirect')],
            'eurr.identifier = t.request_path and eurr.store_id = t.store_id',
            []
        );
        if ($duplicatedRecords = $adapter->fetchCol($select)) {
            $this->source->deleteRecords(
                $this->source->addDocumentPrefix($this->tableName->getTemporaryTableName()),
                'id',
                $duplicatedRecords
            );
        }
        return $duplicatedRecords;
    }
}
