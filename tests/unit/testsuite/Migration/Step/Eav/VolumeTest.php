<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Step\Eav\Volume
     */
    protected $volume;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->initialData = $this->getMockBuilder('\Migration\Step\Eav\InitialData')->disableOriginalConstructor()
            ->setMethods(['getAttributes', 'getAttributeSets', 'getAttributeGroups'])
            ->getMock();
        $this->helper = $this->getMockBuilder('\Migration\Step\Eav\Helper')->disableOriginalConstructor()
            ->setMethods(
                [
                    'getDestinationRecords',
                    'getSourceRecordsCount',
                    'getDestinationRecordsCount',
                    'deleteBackups',
                    'clearIgnoredAttributes'
                ]
            )->getMock();
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['warning', 'addRecord'])
            ->getMock();
        $this->progress = $this->getMockBuilder('\Migration\App\ProgressBar\LogLevelProcessor')
            ->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->readerGroups = $this->getMockBuilder('\Migration\Reader\Groups')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMockBuilder('\Migration\Reader\GroupsFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('eav_document_groups_file')
            ->willReturn($this->readerGroups);
        $this->destination = $this->getMockBuilder('\Migration\ResourceModel\Destination')
            ->disableOriginalConstructor()
            ->setMethods(['getDocument'])
            ->getMock();

        $this->volume = new Volume(
            $this->helper,
            $this->initialData,
            $this->logger,
            $this->progress,
            $groupsFactory,
            $this->destination
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $eavAttributes = [
            'eav_attribute_1' => [
                'attribute_id' => '1',
                'attribute_code' => 'attribute_code_1',
                'attribute_model' => null,
                'backend_model' => null,
                'frontend_model' => null,
                'source_model' => null,
                'frontend_input_renderer' => null,
                'data_model' => null,
                'entity_model' => null,
                'increment_model' => null,
                'entity_attribute_collection' => null,
            ],
            'eav_attribute_2' => [
                'attribute_id' => '2',
                'attribute_code' => 'attribute_code_2',
                'attribute_model' => null,
                'backend_model' => null,
                'frontend_model' => null,
                'source_model' => null,
                'frontend_input_renderer' => null,
                'data_model' => null,
                'entity_model' => null,
                'increment_model' => null,
                'entity_attribute_collection' => null,
            ]
        ];
        $this->progress->expects($this->once())->method('start');
        $this->progress->expects($this->once())->method('finish');
        $this->progress->expects($this->any())->method('advance');

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
        $this->helper->expects($this->once())->method('deleteBackups');
        $this->helper->expects($this->any())->method('clearIgnoredAttributes')->with($eavAttributes)
            ->willReturn($eavAttributes);
        $this->logger->expects($this->never())->method('addRecord');

        $documentsMap = $this->getDocumentsMap();
        $this->destination->expects($this->any())->method('getDocument')->willReturnMap($documentsMap);

        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformWithError()
    {
        $eavAttributes = [
            'eav_attribute_1' => [
                'attribute_id' => '1',
                'attribute_code' => 'attribute_code_1',
                'attribute_model' => 1,
                'backend_model' => 1,
                'frontend_model' => 1,
                'source_model' => 1,
                'frontend_input_renderer' => 1,
                'data_model' => 1,
                'entity_model' => null,
                'increment_model' => null,
                'entity_attribute_collection' => null,
            ],
            'eav_attribute_2' => [
                'attribute_id' => '2',
                'attribute_code' => 'attribute_code_2',
                'attribute_model' => 1,
                'backend_model' => 1,
                'frontend_model' => 1,
                'source_model' => 1,
                'frontend_input_renderer' => 1,
                'data_model' => 1,
                'entity_model' => null,
                'increment_model' => null,
                'entity_attribute_collection' => null,
            ],
        ];
        $this->progress->expects($this->once())->method('start');
        $this->progress->expects($this->once())->method('finish');
        $this->progress->expects($this->any())->method('advance');

        $this->initialData->expects($this->atLeastOnce())->method('getAttributes')->willReturnMap(
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
        $this->helper->expects($this->never())->method('deleteBackups');
        $this->logger->expects($this->atLeastOnce())->method('addRecord');

        $documentsMap = $this->getDocumentsMap();
        $this->destination->expects($this->any())->method('getDocument')->willReturnMap($documentsMap);

        $this->assertFalse($this->volume->perform());
    }

    /**
     * @return array
     */
    protected function getDocumentsMap()
    {
        $structureFields = [
            'eav_attribute' =>
                [
                    'attribute_id' => ['COLUMN_NAME' => 'attribute_id', 'PRIMARY' => true],
                    'field' => ['COLUMN_NAME' => 'field', 'PRIMARY' => false],
                ],
            'catalog_eav_attribute' =>
                [
                    'attribute_id' => ['COLUMN_NAME' => 'attribute_id', 'PRIMARY' => true],
                    'field' => ['COLUMN_NAME' => 'field', 'PRIMARY' => false],
                ],
            'customer_eav_attribute' =>
                [
                    'attribute_id' => ['COLUMN_NAME' => 'attribute_id', 'PRIMARY' => true],
                    'field' => ['COLUMN_NAME' => 'field', 'PRIMARY' => false],
                ],
            'eav_entity_type' =>
                [
                    'attribute_id' => ['COLUMN_NAME' => 'attribute_id', 'PRIMARY' => true],
                    'field' => ['COLUMN_NAME' => 'field', 'PRIMARY' => false],
                ],
        ];
        $documentsMap = [];
        foreach ($structureFields as $documentName => $structure) {
            $structure = new \Migration\ResourceModel\Structure($structureFields[$documentName]);
            $destDocument = $this->getMock('\Migration\ResourceModel\Document', ['getStructure'], [], '', false);
            $destDocument->expects($this->once())->method('getStructure')->willReturn($structure);
            $documentsMap[] = [$documentName, $destDocument];
        }

        return $documentsMap;
    }
}
