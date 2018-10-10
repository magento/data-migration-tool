<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory;

use Migration\App\ProgressBar;
use Migration\App\Step\StageInterface;
use Magento\Framework\Module\ModuleList;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * Progress bar
     *
     * @var ProgressBar\LogLevelProcessor
     */
    private $progress;

    /**
     * @var Model\StockSalesChannel
     */
    private $stockSalesChannel;

    /**
     * @var Model\SourceItem
     */
    private $sourceItem;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @param Model\StockSalesChannel $stockSalesChannel
     * @param Model\SourceItem $sourceItem
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ModuleList $moduleList
     */
    public function __construct(
        Model\StockSalesChannel $stockSalesChannel,
        Model\SourceItem $sourceItem,
        ProgressBar\LogLevelProcessor $progress,
        ModuleList $moduleList
    ) {
        $this->sourceItem = $sourceItem;
        $this->stockSalesChannel = $stockSalesChannel;
        $this->progress = $progress;
        $this->moduleList = $moduleList;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        if (!$this->isInventoryModuleEnabled()) {
            return true;
        }
        $this->progress->start(2);
        $this->progress->advance();
        $this->sourceItem->fill();
        $this->progress->advance();
        $this->stockSalesChannel->fill();
        $this->progress->finish();
        return true;
    }

    /**
     * Check if Inventory module is enabled
     *
     * @param string $moduleName
     * @return bool
     */
    private function isInventoryModuleEnabled()
    {
        return in_array('Magento_Inventory', $this->moduleList->getNames());
    }
}
