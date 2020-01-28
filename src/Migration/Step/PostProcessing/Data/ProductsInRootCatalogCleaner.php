<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\ResourceModel;
use Migration\Step\PostProcessing\Model\ProductsInRootCatalog as ProductsInRootCatalogModel;

/**
 * Class cleans products assigned to tree root category
 *
 * Such assignments are not acceptable by design and should be cleaned
 */
class ProductsInRootCatalogCleaner
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProductsInRootCatalogModel
     */
    private $productsInRootCatalogModel;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Destination $destination
     * @param ProductsInRootCatalogModel $productsInRootCatalogModel
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Destination $destination,
        ProductsInRootCatalogModel $productsInRootCatalogModel
    ) {
        $this->progressBar = $progressBar;
        $this->destination = $destination;
        $this->productsInRootCatalogModel = $productsInRootCatalogModel;
    }

    /**
     * Deletes products assigned to tree root category
     *
     * @return void
     */
    public function clean()
    {
        $productIds = $this->productsInRootCatalogModel->getProductIds();
        if (!$productIds) {
            return ;
        }
        $catalogCategoryProductDocument = $this->destination->addDocumentPrefix(
            $this->productsInRootCatalogModel->getCatalogCategoryProductDocument()
        );
        $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
        $this->destination->deleteRecords(
            $catalogCategoryProductDocument,
            'entity_id',
            $productIds
        );
    }

    /**
     * Get documents
     *
     * @return array
     */
    public function getDocuments()
    {
        return [$this->productsInRootCatalogModel->getCatalogCategoryProductDocument()];
    }
}
