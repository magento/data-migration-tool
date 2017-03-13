<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

class ProductsInRootCatalogCleanerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductsInRootCatalogCleaner
     */
    protected $productsInRootCatalogCleaner;

    /**
     * @var \Migration\Step\PostProcessing\Model\ProductsInRootCatalog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productsInRootCatalogModel;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['deleteRecords'],
            [],
            '',
            false
        );
        $this->productsInRootCatalogModel = $this->getMock(
            'Migration\Step\PostProcessing\Model\ProductsInRootCatalog',
            ['getProductIds', 'getCatalogCategoryProductDocument'],
            [],
            '',
            false
        );
    }

    /**
     * @return void
     */
    public function testClean()
    {
        $productIds = [1, 2, 3];
        $catalogCategoryProductDocument = 'catalog_category_product';
        $this->productsInRootCatalogCleaner = new ProductsInRootCatalogCleaner(
            $this->destination,
            $this->productsInRootCatalogModel
        );
        $this->productsInRootCatalogModel
            ->expects($this->once())
            ->method('getProductIds')
            ->willReturn($productIds);
        $this->productsInRootCatalogModel
            ->expects($this->once())
            ->method('getCatalogCategoryProductDocument')
            ->willReturn($catalogCategoryProductDocument);
        $this->destination
            ->expects($this->once())
            ->method('deleteRecords')
            ->with($catalogCategoryProductDocument, 'entity_id', $productIds);
        $this->productsInRootCatalogCleaner->clean();
    }

    /**
     * @return void
     */
    public function testCleanEmptyProductIds()
    {
        $leftoverAttributeIds = [];
        $this->productsInRootCatalogCleaner = new ProductsInRootCatalogCleaner(
            $this->destination,
            $this->productsInRootCatalogModel
        );
        $this->productsInRootCatalogModel
            ->expects($this->once())
            ->method('getProductIds')
            ->willReturn($leftoverAttributeIds);
        $this->productsInRootCatalogModel->expects($this->never())->method('getCatalogCategoryProductDocument');
        $this->destination->expects($this->never())->method('deleteRecords');
        $this->productsInRootCatalogCleaner->clean();
    }
}
