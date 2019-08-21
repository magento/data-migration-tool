<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite\Model;

/**
 * Class TemporaryTableName
 */
class TemporaryTableName
{
    /**
     * @var string
     */
    private $tableName = '';

    /**
     * TemporaryTableName constructor
     */
    public function __construct()
    {
        $this->tableName = 'url_rewrite_m2' . md5('url_rewrite_m2');
    }

    /**
     * Return name of temporary table
     *
     * @return string
     */
    public function getName()
    {
        return $this->tableName;
    }
}
