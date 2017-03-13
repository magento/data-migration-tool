<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

class EavLeftoverDataCleanerTest extends \PHPUnit_Framework_TestCase
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
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['deleteRecords', 'getRecordsCount'],
            [],
            '',
            false
        );
        $this->progressBar = $this->getMock(
            'Migration\App\ProgressBar\LogLevelProcessor',
            ['advance'],
            [],
            '',
            false
        );
        $this->progress = $this->getMock(
            'Migration\App\Progress',
            ['saveProcessedEntities'],
            [],
            '',
            false
        );
        $this->eavLeftoverDataModel = $this->getMock(
            'Migration\Step\PostProcessing\Model\EavLeftoverData',
            ['getDocumentsToCheck', 'getLeftoverAttributeIds'],
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
            ->method('getDocumentsToCheck')
            ->willReturn($documentsToCheck);
        $this->progressBar
            ->expects($this->exactly(2))
            ->method('advance')
            ->with('info');
        $this->destination
            ->expects($this->at(0))
            ->method('getRecordsCount')
            ->with('doc1')
            ->willReturn(5);
        $this->destination
            ->expects($this->at(1))
            ->method('deleteRecords')
            ->with('doc1', 'attribute_id', $leftoverAttributeIds);
        $this->destination->expects($this->at(2))->method('getRecordsCount')->with('doc1')->willReturn(3);
        $this->destination->expects($this->at(3))->method('getRecordsCount')->with('doc2')->willReturn(4);
        $this->destination
            ->expects($this->at(4))
            ->method('deleteRecords')
            ->with('doc2', 'attribute_id', $leftoverAttributeIds);
        $this->destination->expects($this->at(5))->method('getRecordsCount')->with('doc2')->willReturn(1);
        $this->progress
            ->expects($this->once())
            ->method('saveProcessedEntities')
            ->with('PostProcessing', 'deletedDocumentRowsCount', $deletedDocumentRowsCount);
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
        $this->eavLeftoverDataModel->expects($this->never())->method('getDocumentsToCheck');
        $this->progressBar->expects($this->never())->method('advance')->with('info');
        $this->destination->expects($this->never())->method('getRecordsCount');
        $this->destination->expects($this->never())->method('deleteRecords');
        $this->progress->expects($this->never())->method('saveProcessedEntities');
        $this->eavLeftoverDataCleaner->clean();
    }

    /**
     * @return void
     */
    public function testGetIterationsCount()
    {
        $documentsToCheck = ['doc1', 'doc2'];
        $this->eavLeftoverDataCleaner = new EavLeftoverDataCleaner(
            $this->progressBar,
            $this->destination,
            $this->progress,
            $this->eavLeftoverDataModel
        );
        $this->eavLeftoverDataModel
            ->expects($this->once())
            ->method('getDocumentsToCheck')
            ->willReturn($documentsToCheck);
        $this->assertEquals(2, $this->eavLeftoverDataCleaner->getIterationsCount());
    }
}
