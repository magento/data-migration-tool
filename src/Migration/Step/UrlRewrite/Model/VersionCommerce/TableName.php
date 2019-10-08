<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model\VersionCommerce;

/**
 * Class TableName
 */
class TableName
{
    /**
     * @var string
     */
    private $temporaryTableName = '';

    /**
     * @var string
     */
    private $destinationTableName = 'url_rewrite';

    /**
     * @var string
     */
    private $destinationProductCategoryTableName = 'catalog_url_rewrite_product_category';

    /**
     * TemporaryTableName constructor
     */
    public function __construct()
    {
        $this->temporaryTableName = 'url_rewrite_m2' . md5('url_rewrite_m2');
    }

    /**
     * Return name of temporary table
     *
     * @return string
     */
    public function getTemporaryTableName()
    {
        return $this->temporaryTableName;
    }

    /**
     * Return name of url rewrite table
     *
     * @return string
     */
    public function getDestinationTableName()
    {
        return $this->destinationTableName;
    }

    /**
     * Return name of url rewrite product category table
     *
     * @return string
     */
    public function getDestinationProductCategoryTableName()
    {
        return $this->destinationProductCategoryTableName;
    }
}
