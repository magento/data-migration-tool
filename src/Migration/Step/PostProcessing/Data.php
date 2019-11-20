<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing;

use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Step\PostProcessing\Data\EavLeftoverDataCleaner;
use Migration\Step\PostProcessing\Data\AttributeSetLeftoverDataCleaner;
use Migration\Step\PostProcessing\Data\ProductsInRootCatalogCleaner;
use Migration\Step\PostProcessing\Data\EntityTypeTextToVarcharMover;
use Migration\Step\PostProcessing\Data\DeletedRecordsCounter;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * @var EavLeftoverDataCleaner
     */
    private $eavLeftoverDataCleaner;

    /**
     * @var AttributeSetLeftoverDataCleaner
     */
    private $attributeSetLeftoverDataCleaner;

    /**
     * @var ProductsInRootCatalogCleaner
     */
    private $productsInRootCatalogCleaner;

    /**
     * @var EntityTypeTextToVarcharMover
     */
    private $entityTypeTextToVarcharMover;

    /**
     * @var DeletedRecordsCounter
     */
    private $deletedRecordsCounter;

    /**
     * @var array
     */
    private $documents = [];

    /**
     * Data constructor.
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param EavLeftoverDataCleaner $eavLeftoverDataCleaner
     * @param AttributeSetLeftoverDataCleaner $attributeSetLeftoverDataCleaner
     * @param ProductsInRootCatalogCleaner $productsInRootCatalogCleaner
     * @param EntityTypeTextToVarcharMover $entityTypeTextToVarcharMover
     * @param DeletedRecordsCounter $deletedRecordsCounter
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        EavLeftoverDataCleaner $eavLeftoverDataCleaner,
        AttributeSetLeftoverDataCleaner $attributeSetLeftoverDataCleaner,
        ProductsInRootCatalogCleaner $productsInRootCatalogCleaner,
        EntityTypeTextToVarcharMover $entityTypeTextToVarcharMover,
        DeletedRecordsCounter $deletedRecordsCounter
    ) {
        $this->progressBar = $progressBar;
        $this->eavLeftoverDataCleaner = $eavLeftoverDataCleaner;
        $this->attributeSetLeftoverDataCleaner = $attributeSetLeftoverDataCleaner;
        $this->productsInRootCatalogCleaner = $productsInRootCatalogCleaner;
        $this->entityTypeTextToVarcharMover = $entityTypeTextToVarcharMover;
        $this->deletedRecordsCounter = $deletedRecordsCounter;
        $append = function ($document) {
            $this->documents[] = $document;
        };
        array_map($append, $this->eavLeftoverDataCleaner->getDocuments());
        array_map($append, $this->attributeSetLeftoverDataCleaner->getDocuments());
        array_map($append, $this->productsInRootCatalogCleaner->getDocuments());
        array_map($append, $this->entityTypeTextToVarcharMover->getDocuments());
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progressBar->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $this->eavLeftoverDataCleaner->clean();
        $this->attributeSetLeftoverDataCleaner->clean();
        $this->productsInRootCatalogCleaner->clean();
        $this->entityTypeTextToVarcharMover->move();
        $this->deletedRecordsCounter->saveChanged($this->documents);
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Get iterations count
     *
     * @return int
     */
    private function getIterationsCount()
    {
        return count($this->documents);
    }
}
