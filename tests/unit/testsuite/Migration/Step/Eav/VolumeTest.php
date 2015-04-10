<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Class VolumeTest
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Eav\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Migration\Step\Eav\InitialData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $initialData;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\MapReader\MapReaderEav|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    /**
     * @var \Migration\Step\Eav\Volume
     */
    protected $volume;

    public function setUp()
    {
        $this->initialData = $this->getMockBuilder('\Migration\Step\Eav\InitialData')->disableOriginalConstructor()
            ->setMethods(['getAttributes', 'getAttributeSets', 'getAttributeGroups'])
            ->getMock();
        $this->helper = $this->getMockBuilder('\Migration\Step\Eav\Helper')->disableOriginalConstructor()
            ->setMethods(['getDestinationRecords', 'getSourceRecordsCount', 'getDestinationRecordsCount'])
            ->getMock();
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['log'])
            ->getMock();
        $this->progress = $this->getMockBuilder('\Migration\ProgressBar')->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->mapReader = $this->getMockBuilder('\Migration\MapReader\MapReaderEav')->disableOriginalConstructor()
            ->setMethods(['getDocumentsMap', 'getJustCopyDocuments'])
            ->getMock();
        $this->volume = new Volume($this->helper, $this->initialData, $this->logger, $this->progress, $this->mapReader);
    }

    public function testPerform()
    {
        $documentsMap = [
            'source_document_1' => 'destination_document_1',
            'source_document_2' => 'destination_document_2'
        ];
        $eavAttributes = [
            'eav_attribute_1' => [
                'attribute_id' => '1',
                'attribute_code' => 'attribute_code_1',
                'attribute_model' => null,
                'backend_model' => null,
                'frontend_model' => null,
                'source_model' => null,
                'frontend_input_renderer' => null,
                'data_model' => null],
            'eav_attribute_2' => [
                'attribute_id' => '2',
                'attribute_code' => 'attribute_code_2',
                'attribute_model' => null,
                'backend_model' => null,
                'frontend_model' => null,
                'source_model' => null,
                'frontend_input_renderer' => null,
                'data_model' => null]
        ];
        $this->progress->expects($this->once())->method('start');
        $this->progress->expects($this->once())->method('finish');
        $this->progress->expects($this->any())->method('advance');
        $this->mapReader->expects($this->any())->method('getDocumentsMap')->willReturn($documentsMap);

        $this->initialData->expects($this->any())->method('getAttributes')->willReturnMap(
            [
                ['source', $eavAttributes],
                ['destination', $eavAttributes]
            ]
        );
        $this->helper->expects($this->any())->method('getDestinationRecords')->willReturn($eavAttributes);
        $this->helper->expects($this->any())->method('getSourceRecordsCount')->willReturnMap(
            [
                ['eav_attribute_set', 1],
                ['eav_attribute_group', 1],
                ['copy_document_1', 2],
                ['copy_document_2', 2]
            ]
        );
        $this->initialData->expects($this->once())->method('getAttributeSets')->willReturn(1);
        $this->initialData->expects($this->once())->method('getAttributeGroups')->willReturn(1);
        $this->helper->expects($this->any())->method('getDestinationRecordsCount')->willReturn(2);
        $justCopyDocuments = ['copy_document_1', 'copy_document_2'];
        $this->mapReader->expects($this->any())->method('getJustCopyDocuments')->willReturn($justCopyDocuments);
        $this->logger->expects($this->never())->method('log');

        $this->assertTrue($this->volume->perform());
    }

    public function testPerformWithError()
    {
        $documentsMap = [
            'source_document_1' => 'destination_document_1',
            'source_document_2' => 'destination_document_2'
        ];
        $eavAttributes = [
            'eav_attribute_1' => [
                'attribute_id' => '1',
                'attribute_code' => 'attribute_code_1',
                'attribute_model' => 1,
                'backend_model' => 1,
                'frontend_model' => 1,
                'source_model' => 1,
                'frontend_input_renderer' => 1,
                'data_model' => 1],
            'eav_attribute_2' => [
                'attribute_id' => '2',
                'attribute_code' => 'attribute_code_2',
                'attribute_model' => 1,
                'backend_model' => 1,
                'frontend_model' => 1,
                'source_model' => 1,
                'frontend_input_renderer' => 1,
                'data_model' => 1]
        ];
        $this->progress->expects($this->once())->method('start');
        $this->progress->expects($this->once())->method('finish');
        $this->progress->expects($this->any())->method('advance');
        $this->mapReader->expects($this->any())->method('getDocumentsMap')->willReturn($documentsMap);

        $this->initialData->expects($this->any())->method('getAttributes')->willReturnMap(
            [
                ['source', $eavAttributes],
                ['destination', $eavAttributes]
            ]
        );
        $this->helper->expects($this->any())->method('getDestinationRecords')->willReturn($eavAttributes);
        $this->helper->expects($this->any())->method('getSourceRecordsCount')->willReturnMap(
            [
                ['eav_attribute_set', 1],
                ['eav_attribute_group', 1],
                ['copy_document_1', 2],
                ['copy_document_2', 2]
            ]
        );
        $this->initialData->expects($this->once())->method('getAttributeSets')->willReturn(1);
        $this->initialData->expects($this->once())->method('getAttributeGroups')->willReturn(1);
        $this->helper->expects($this->any())->method('getDestinationRecordsCount')->willReturn(1);
        $justCopyDocuments = ['copy_document_1', 'copy_document_2'];
        $this->mapReader->expects($this->any())->method('getJustCopyDocuments')->willReturn($justCopyDocuments);
        $this->logger->expects($this->atLeastOnce())->method('log');

        $this->assertFalse($this->volume->perform());
    }
}
