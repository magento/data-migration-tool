<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param \Magento\Framework\DB\Select $select
     * @return array
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select);
}
