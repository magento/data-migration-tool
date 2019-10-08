<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\Inventory;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;

/**
 * Class Delta
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_inventory';

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
     * @var array
     */
    private $deltaTablesMap = [];

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Model\SourceItem $sourceItem
     * @param Model\ShipmentSource $shipmentSource
     * @param Model\InventoryModule $inventoryModule
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Model\SourceItem $sourceItem,
        Model\ShipmentSource $shipmentSource,
        Model\InventoryModule $inventoryModule
    ) {
        $this->sourceItem = $sourceItem;
        $this->shipmentSource = $shipmentSource;
        $this->inventoryModule = $inventoryModule;
        $this->deltaTablesMap = [
            'cataloginventory_stock_item' => ['field' => 'product_id', 'model' => $this->sourceItem],
            'sales_flat_shipment' => ['field' => 'entity_id', 'model' => $this->shipmentSource]
        ];
        parent::__construct(
            $source,
            $mapFactory,
            $groupsFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory
        );
    }

    /**
     * @inheritdoc
     */
    protected function processChangedRecords($documentName, $idKeys)
    {
        if (!$this->inventoryModule->isInventoryModuleEnabled()
            || !in_array($documentName, array_keys($this->deltaTablesMap))
        ) {
            return;
        }
        $page = 0;
        $ids = [];
        /** @var Model\InventoryModelInterface $inventoryModel */
        $inventoryModel = $this->deltaTablesMap[$documentName]['model'];
        $fieldId = $this->deltaTablesMap[$documentName]['field'];
        while (!empty($items = $this->source->getChangedRecords($documentName, $idKeys, $page++, true))) {
            foreach ($items as $item) {
                $ids[] = $item[$fieldId];
                echo('.');
            }
            $select = $inventoryModel->prepareSelect()->where($fieldId . ' IN(?)', $ids);
            $inventoryModel->insertFromSelect($select);
        }
    }
}
