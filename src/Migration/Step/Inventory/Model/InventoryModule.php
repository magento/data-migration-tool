<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory\Model;

use Magento\Framework\Module\ModuleList;

/**
 * Class InventoryModule
 */
class InventoryModule
{
    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @param ModuleList $moduleList
     */
    public function __construct(
        ModuleList $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if Inventory module is enabled
     *
     * @return bool
     */
    public function isInventoryModuleEnabled()
    {
        return in_array('Magento_Inventory', $this->moduleList->getNames());
    }
}
