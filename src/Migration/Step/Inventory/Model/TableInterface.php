<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

/**
 * Interface TableInterface
 */
interface TableInterface
{
    /**
     * Return name of destination table
     *
     * @return string
     */
    public function getDestinationTableName();

    /**
     * Return names of destination fields
     *
     * @return array
     */
    public function getDestinationTableFields();

    /**
     * Return name of table from which data is fetched
     *
     * @return string
     */
    public function getSourceTableName();
}
