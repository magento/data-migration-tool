<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

class EavLeftoverDataCleanerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EavLeftoverDataCleaner
     */
    protected $eavLeftoverDataCleaner;

    /**
     * @var \Migration\Step\PostProcessing\Model\EavLeftoverData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavLeftoverDataModel;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progressBar;

    /**
     * @var \Migration\App\Progress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['deleteRecords', 'getRecordsCount']
        );
        $this->progressBar = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['advance']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\Progress::class,
            []
        );
        $this->eavLeftoverDataModel = $this->createPartialMock(
            \Migration\Step\PostProcessing\Model\EavLeftoverData::class,
            ['getDocuments', 'getLeftoverAttributeIds']
        );
    }

    /**
     * @return void
     */
    public function testClean()
    {
        $leftoverAttributeIds = [1, 2, 3];
        $documentsToCheck = ['doc1', 'doc2'];
        $deletedDocumentRowsCount = ['doc1' => 2, 'doc2' => 3];
        $this->eavLeftoverDataCleaner = new EavLeftoverDataCleaner(
            $this->progressBar,
            $this->destination,
            $this->progress,
            $this->eavLeftoverDataModel
        );
        $this->eavLeftoverDataModel
            ->expects($this->once())
            ->method('getLeftoverAttributeIds')
            ->willReturn($leftoverAttributeIds);
        $this->eavLeftoverDataModel
            ->expects($this->once())
            ->method('getDocuments')
            ->willReturn($documentsToCheck);
        $this->progressBar
            ->expects($this->exactly(2))
            ->method('advance')
            ->with('info');
        $this->destination
            ->expects($this->at(0))
            ->method('deleteRecords')
            ->with('doc1', 'attribute_id', $leftoverAttributeIds);
        $this->destination
            ->expects($this->at(1))
            ->method('deleteRecords')
            ->with('doc2', 'attribute_id', $leftoverAttributeIds);
        $this->eavLeftoverDataCleaner->clean();
    }

    /**
     * @return void
     */
    public function testCleanEmptyAttributeIds()
    {
        $leftoverAttributeIds = [];
        $this->eavLeftoverDataCleaner = new EavLeftoverDataCleaner(
            $this->progressBar,
            $this->destination,
            $this->progress,
            $this->eavLeftoverDataModel
        );
        $this->eavLeftoverDataModel
            ->expects($this->once())
            ->method('getLeftoverAttributeIds')
            ->willReturn($leftoverAttributeIds);
        $this->eavLeftoverDataModel->expects($this->never())->method('getDocuments');
        $this->progressBar->expects($this->never())->method('advance')->with('info');
        $this->destination->expects($this->never())->method('deleteRecords');
        $this->eavLeftoverDataCleaner->clean();
    }
}
