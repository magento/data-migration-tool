<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

/**
 * Interface InventoryModelInterface
 */
interface InventoryModelInterface
{
    /**
     * Prepare select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function prepareSelect();

    /**
     * Insert from select
     *
     * @return array
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select);
}
