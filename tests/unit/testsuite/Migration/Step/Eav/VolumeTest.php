<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Class VolumeTest
 */
class VolumeTest extends \PHPUnit\Framework\TestCase
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
     * @var \Migration\Step\Eav\Model\IgnoredAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ignoredAttributes;

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
        $this->initialData = $this->getMockBuilder(\Migration\Step\Eav\InitialData::class)->disableOriginalConstructor()
            ->setMethods(['getAttributes', 'getAttributeSets', 'getAttributeGroups'])
            ->getMock();
        $this->helper = $this->getMockBuilder(\Migration\Step\Eav\Helper::class)->disableOriginalConstructor()
            ->setMethods(
                [
                    'getDestinationRecords',
                    'getSourceRecordsCount',
                    'getDestinationRecordsCount',
                    'deleteBackups'
                ]
            )->getMock();
        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)->disableOriginalConstructor()
            ->setMethods(['warning', 'addRecord'])
            ->getMock();
        $this->progress = $this->getMockBuilder(\Migration\App\ProgressBar\LogLevelProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->readerGroups = $this->getMockBuilder(\Migration\Reader\Groups::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMockBuilder(\Migration\Reader\GroupsFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('eav_document_groups_file')
            ->willReturn($this->readerGroups);
        $this->destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDocument'])
            ->getMock();
        $this->ignoredAttributes = $this->getMockBuilder(\Migration\Step\Eav\Model\IgnoredAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearIgnoredAttributes'])
            ->getMock();

        $this->volume = new Volume(
            $this->helper,
            $this->initialData,
            $this->ignoredAttributes,
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
        $this->ignoredAttributes->expects($this->any())->method('clearIgnoredAttributes')->with($eavAttributes)
            ->willReturn($eavAttributes);
        $this->logger->expects($this->never())->method('addRecord');

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

        $this->assertFalse($this->volume->perform());
    }
}
