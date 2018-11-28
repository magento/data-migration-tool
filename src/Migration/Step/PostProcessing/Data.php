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
use Migration\Step\PostProcessing\Data\ProductsInRootCatalogCleaner;

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
     * @var ProductsInRootCatalogCleaner
     */
    private $productsInRootCatalogCleaner;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param EavLeftoverDataCleaner $eavLeftoverDataCleaner
     * @param ProductsInRootCatalogCleaner $productsInRootCatalogCleaner
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        EavLeftoverDataCleaner $eavLeftoverDataCleaner,
        ProductsInRootCatalogCleaner $productsInRootCatalogCleaner
    ) {
        $this->progressBar = $progressBar;
        $this->eavLeftoverDataCleaner = $eavLeftoverDataCleaner;
        $this->productsInRootCatalogCleaner = $productsInRootCatalogCleaner;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progressBar->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $this->eavLeftoverDataCleaner->clean();
        $this->productsInRootCatalogCleaner->clean();
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
        return $this->eavLeftoverDataCleaner->getIterationsCount();
    }
}
