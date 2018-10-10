<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
use Magento\Framework\Module\ModuleList;

/**
 * Class Integrity
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
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @param Destination $destination
     * @param ResourceModel\Source $source
     * @param Logger $logger
     * @param Config $config
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Model\StockSalesChannel $stockSalesChannel
     * @param Model\SourceItem $sourceItem
     * @param ModuleList $moduleList
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
        ModuleList $moduleList,
        MapFactory $mapFactory,
        $mapConfigOption = 'map_file'
    ) {
        $this->moduleList = $moduleList;
        $this->sourceItem = $sourceItem;
        $this->stockSalesChannel = $stockSalesChannel;
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        if (!$this->isInventoryModuleEnabled()) {
            return true;
        }
        $this->progress->start(1);
        $this->progress->advance();
        $sourceItemTable = $this->sourceItem->getSourceItemTable();
        if (!$this->destination->getDocument($sourceItemTable)) {
            $this->missingDocuments[MapInterface::TYPE_DEST][$sourceItemTable] = true;
        } else {
            $structureExistingSourceItemTable = array_keys(
                $this->destination
                    ->getDocument($sourceItemTable)
                    ->getStructure()
                    ->getFields()
            );
            $this->checkStructure(
                $sourceItemTable,
                $this->sourceItem->getSourceItemTableFields(),
                $structureExistingSourceItemTable
            );
        }

        $stockSalesChannelTable = $this->stockSalesChannel->getStockSalesChannelTable();
        if (!$this->destination->getDocument($stockSalesChannelTable)) {
            $this->missingDocuments[MapInterface::TYPE_DEST][$stockSalesChannelTable] = true;
        } else {
            $structureExistingStockSalesChannelTable = array_keys(
                $this->destination
                    ->getDocument($stockSalesChannelTable)
                    ->getStructure()
                    ->getFields()
            );
            $this->checkStructure(
                $stockSalesChannelTable,
                $this->stockSalesChannel->getStockSalesChannelTableFields(),
                $structureExistingStockSalesChannelTable
            );
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
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
    }

    /**
     * {@inheritdoc}
     */
    protected function checkForErrors()
    {
        $checkDocuments = $this->checkDocuments();
        $checkDocumentFields = $this->checkDocumentFields();
        return $checkDocuments && $checkDocumentFields;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIterationsCount()
    {
        return 0;
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
