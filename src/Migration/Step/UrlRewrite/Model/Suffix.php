<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model;

use Migration\ResourceModel\Source;

/**
 * Class Suffix can generate part of SQL query
 * which determine suffix for products or categories
 * depending on scope (default, website, store)
 */
class Suffix
{
    /**
     * @var Source
     */
    private $source;

    /**
     * @var array
     */
    private $suffixData = [];

    /**
     * @param Source $source
     */
    public function __construct(
        Source $source
    ) {
        $this->source = $source;
    }

    /**
     * Get suffix query for product or category
     *
     * @param string $suffixFor Can be 'product' or 'category'
     * @param string $mainTable
     * @return string
     */
    public function getSuffix($suffixFor, $mainTable = 's')
    {
        $suffixDefault = '.html';
        if (empty($this->suffixData[$suffixFor])) {
            /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $select = $adapter->getSelect();

            $select->from(
                ['s' => $this->source->addDocumentPrefix('core_store')],
                ['store_id' => 's.store_id']
            );

            $select->joinLeft(
                ['c1' => $this->source->addDocumentPrefix('core_config_data')],
                "c1.scope='stores' AND c1.path = 'catalog/seo/{$suffixFor}_url_suffix' AND c1.scope_id=s.store_id",
                ['store_path' => 'c1.path', 'store_value' => 'c1.value']
            );
            $select->joinLeft(
                ['c2' => $this->source->addDocumentPrefix('core_config_data')],
                "c2.scope='websites' AND c2.path = 'catalog/seo/{$suffixFor}_url_suffix' AND c2.scope_id=s.website_id",
                ['website_path' => 'c2.path', 'website_value' => 'c2.value']
            );
            $select->joinLeft(
                ['c3' => $this->source->addDocumentPrefix('core_config_data')],
                "c3.scope='default' AND c3.path = 'catalog/seo/{$suffixFor}_url_suffix'",
                ['admin_path' => 'c3.path', 'admin_value' => 'c3.value']
            );

            $result = $select->getAdapter()->fetchAll($select);
            foreach ($result as $row) {
                $suffix = $suffixDefault;
                if ($row['admin_path'] !== null) {
                    $suffix = $row['admin_value'];
                }
                if ($row['website_path'] !== null) {
                    $suffix = $row['website_value'];
                }
                if ($row['store_path'] !== null) {
                    $suffix = $row['store_value'];
                }
                $suffix = ($suffix) ? $this->ensureSuffixBeginsWithDot($suffix) : $suffix;
                $this->suffixData[$suffixFor][] = [
                    'store_id' => $row['store_id'],
                    'suffix' => $suffix
                ];
            }
        }

        $suffix = "CASE {$mainTable}.store_id";
        foreach ($this->suffixData[$suffixFor] as $row) {
            $suffix .= sprintf(" WHEN '%s' THEN '%s'", $row['store_id'], $row['suffix']);
        }
        $suffix .= " ELSE '{$suffixDefault}' END";

        return $suffix;
    }

    /**
     * Ensure suffix begins with dot
     *
     * @param mixed $suffix
     * @return string
     */
    private function ensureSuffixBeginsWithDot($suffix)
    {
        return substr($suffix, 0, 1) === "." ? $suffix : '.' . $suffix;
    }
}
