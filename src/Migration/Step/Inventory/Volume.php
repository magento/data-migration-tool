<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\Step\Customer\Model\SourceRecordsCounter;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
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
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progress;

    /**
     * @param Model\SourceItem $sourceItem
     * @param Model\ShipmentSource $shipmentSource
     * @param Model\InventoryModule $inventoryModule
     * @param Logger $logger
     * @param ResourceModel\Destination $destination
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Model\SourceItem $sourceItem,
        Model\ShipmentSource $shipmentSource,
        Model\InventoryModule $inventoryModule,
        Logger $logger,
        ResourceModel\Destination $destination,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->sourceItem = $sourceItem;
        $this->shipmentSource = $shipmentSource;
        $this->inventoryModule = $inventoryModule;
        $this->destination = $destination;
        $this->progress = $progress;
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        if (!$this->inventoryModule->isInventoryModuleEnabled()) {
            return true;
        }
        $inventoryModels = [$this->sourceItem, $this->shipmentSource];
        $this->progress->start(count($inventoryModels));
        /** @var Model\TableInterface $inventoryModel */
        foreach ($inventoryModels as $inventoryModel) {
            $this->progress->advance();
            $sourceCount = $this->destination->getRecordsCount($inventoryModel->getSourceTableName());
            $destinationCount = $this->destination->getRecordsCount($inventoryModel->getDestinationTableName());
            if ($sourceCount != $destinationCount) {
                $this->errors[] = sprintf(
                    'Mismatch of entities in the document: %s Source: %s Destination: %s',
                    $inventoryModel->getDestinationTableName(),
                    $sourceCount,
                    $destinationCount
                );
            }
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }
}
