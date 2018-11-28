<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory;

use Migration\App\ProgressBar;
use Migration\App\Step\StageInterface;

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
     * @var Model\ShipmentSource
     */
    private $shipmentSource;

    /**
     * @var Model\InventoryModule
     */
    private $inventoryModule;

    /**
     * @param Model\StockSalesChannel $stockSalesChannel
     * @param Model\SourceItem $sourceItem
     * @param Model\InventoryModule $inventoryModule
     * @param Model\ShipmentSource $shipmentSource
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Model\StockSalesChannel $stockSalesChannel,
        Model\SourceItem $sourceItem,
        Model\InventoryModule $inventoryModule,
        Model\ShipmentSource $shipmentSource,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->sourceItem = $sourceItem;
        $this->stockSalesChannel = $stockSalesChannel;
        $this->shipmentSource = $shipmentSource;
        $this->progress = $progress;
        $this->inventoryModule = $inventoryModule;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        if (!$this->inventoryModule->isInventoryModuleEnabled()) {
            return true;
        }
        $inventoryModels = [$this->sourceItem, $this->stockSalesChannel, $this->shipmentSource];
        $this->progress->start(count($inventoryModels));
        /** @var Model\InventoryModelInterface $inventoryModel */
        foreach ($inventoryModels as $inventoryModel) {
            $inventoryModel->insertFromSelect($inventoryModel->prepareSelect());
            $this->progress->advance();
        }
        $this->progress->finish();
        return true;
    }
}
