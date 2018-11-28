<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Inventory;

use Migration\Reader\MapInterface;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;
use Migration\Logger\Logger;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\Config;

/**
 * Class Integrity
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
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
     * @param Destination $destination
     * @param ResourceModel\Source $source
     * @param Logger $logger
     * @param Config $config
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Model\StockSalesChannel $stockSalesChannel
     * @param Model\SourceItem $sourceItem
     * @param Model\ShipmentSource $shipmentSource
     * @param Model\InventoryModule $inventoryModule
     * @param MapFactory $mapFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        Destination $destination,
        ResourceModel\Source $source,
        Logger $logger,
        Config $config,
        ProgressBar\LogLevelProcessor $progress,
        Model\StockSalesChannel $stockSalesChannel,
        Model\SourceItem $sourceItem,
        Model\ShipmentSource $shipmentSource,
        Model\InventoryModule $inventoryModule,
        MapFactory $mapFactory,
        $mapConfigOption = 'map_file'
    ) {
        $this->sourceItem = $sourceItem;
        $this->stockSalesChannel = $stockSalesChannel;
        $this->shipmentSource = $shipmentSource;
        $this->inventoryModule = $inventoryModule;
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
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
        /** @var Model\TableInterface $inventoryModel */
        foreach ($inventoryModels as $inventoryModel) {
            $tableName = $inventoryModel->getDestinationTableName();
            $tableFields = $inventoryModel->getDestinationTableFields();
            if (!$this->destination->getDocument($tableName)) {
                $this->missingDocuments[MapInterface::TYPE_DEST][$tableName] = true;
            } else {
                $structureExistingTable = array_keys(
                    $this->destination
                        ->getDocument($tableName)
                        ->getStructure()
                        ->getFields()
                );
                $this->checkStructure(
                    $tableName,
                    $tableFields,
                    $structureExistingTable
                );
            }
            $this->progress->advance();
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Check structure
     *
     * @param string $documentName
     * @param array $source
     * @param array $destination
     * @return void
     */
    private function checkStructure($documentName, array $source, array $destination)
    {
        $fieldsDiff = array_diff($source, $destination);
        if ($fieldsDiff) {
            $this->missingDocumentFields[MapInterface::TYPE_DEST][$documentName] = $fieldsDiff;
        }
        $fieldsDiff = array_diff($destination, $source);
        if ($fieldsDiff) {
            $this->missingDocumentFields[MapInterface::TYPE_SOURCE][$documentName] = $fieldsDiff;
        }
    }

    /**
     * @inheritdoc
     */
    protected function checkForErrors()
    {
        $checkDocuments = $this->checkDocuments();
        $checkDocumentFields = $this->checkDocumentFields();
        return $checkDocuments && $checkDocumentFields;
    }

    /**
     * @inheritdoc
     */
    protected function getIterationsCount()
    {
        return 0;
    }
}
