<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\Step\PostProcessing\Data\EavLeftoverDataCleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavLeftoverDataCleaner;

    /**
     * @var \Migration\Step\PostProcessing\Data\ProductsInRootCatalogCleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productsInRootCatalogCleaner;

    /**
     * @var \Migration\Step\PostProcessing\Data\AttributeSetLeftoverDataCleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetLeftoverDataCleaner;

    /**
     * @var \Migration\Step\PostProcessing\Data\DeletedRecordsCounter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deletedRecordsCounter;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->eavLeftoverDataCleaner = $this->createPartialMock(
            \Migration\Step\PostProcessing\Data\EavLeftoverDataCleaner::class,
            ['clean', 'getDocuments']
        );
        $this->productsInRootCatalogCleaner = $this->createPartialMock(
            \Migration\Step\PostProcessing\Data\ProductsInRootCatalogCleaner::class,
            ['clean', 'getDocuments']
        );
        $this->attributeSetLeftoverDataCleaner = $this->createPartialMock(
            \Migration\Step\PostProcessing\Data\AttributeSetLeftoverDataCleaner::class,
            ['clean', 'getDocuments']
        );
        $this->deletedRecordsCounter = $this->createPartialMock(
            \Migration\Step\PostProcessing\Data\DeletedRecordsCounter::class,
            ['saveDeleted', 'count']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish']
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $documents1 = ['doc11', 'doc12'];
        $documents2 = ['doc21', 'doc22'];
        $documents3 = ['doc31', 'doc32'];
        $documents = array_merge($documents1, $documents2, $documents3);
        $this->deletedRecordsCounter->expects($this->once())->method('count')->with($documents);
        $this->deletedRecordsCounter->expects($this->once())->method('saveDeleted')->with($documents);
        $this->progress
            ->expects($this->once())
            ->method('start')
            ->with(count($documents), \Migration\Logger\Manager::LOG_LEVEL_INFO);
        $this->progress->expects($this->once())->method('finish');
        $this->eavLeftoverDataCleaner->expects($this->any())->method('getDocuments')->willReturn($documents1);
        $this->attributeSetLeftoverDataCleaner->expects($this->any())->method('getDocuments')->willReturn($documents2);
        $this->productsInRootCatalogCleaner->expects($this->any())->method('getDocuments')->willReturn($documents3);
        $this->eavLeftoverDataCleaner->expects($this->once())->method('clean');
        $this->productsInRootCatalogCleaner->expects($this->once())->method('clean');
        $this->attributeSetLeftoverDataCleaner->expects($this->once())->method('clean');
        $this->data = new Data(
            $this->progress,
            $this->eavLeftoverDataCleaner,
            $this->attributeSetLeftoverDataCleaner,
            $this->productsInRootCatalogCleaner,
            $this->deletedRecordsCounter
        );
        $this->data->perform();
    }
}
