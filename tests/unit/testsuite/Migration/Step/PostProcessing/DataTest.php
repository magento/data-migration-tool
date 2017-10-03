<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            ['clean', 'getIterationsCount']
        );
        $this->productsInRootCatalogCleaner = $this->createPartialMock(
            \Migration\Step\PostProcessing\Data\ProductsInRootCatalogCleaner::class,
            ['clean']
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
        $iterationsCount = 1;
        $this->data = new Data($this->progress, $this->eavLeftoverDataCleaner, $this->productsInRootCatalogCleaner);
        $this->progress->expects($this->once())->method('start')->with($iterationsCount);
        $this->progress->expects($this->once())->method('finish');
        $this->eavLeftoverDataCleaner->expects($this->once())->method('clean');
        $this->eavLeftoverDataCleaner
            ->expects($this->once())
            ->method('getIterationsCount')
            ->willReturn($iterationsCount);
        $this->productsInRootCatalogCleaner->expects($this->once())->method('clean');
        $this->data->perform();
    }
}
