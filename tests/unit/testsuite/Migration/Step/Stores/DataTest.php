<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\RecordTransformerFactory;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;

/**
 * Class DataTest
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    protected $data;
    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var \Migration\Step\Stores\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->getMockBuilder('Migration\App\ProgressBar\LogLevelProcessor')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->source = $this->getMockBuilder('Migration\ResourceModel\Source')
            ->disableOriginalConstructor()
            ->getMock();
        $this->destination = $this->getMockBuilder('Migration\ResourceModel\Destination')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->helper = $this->getMockBuilder('Migration\Step\Stores\Helper')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->recordFactory = $this->getMockBuilder('Migration\ResourceModel\RecordFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $recordsData = [
            'record_1' => ['field_name' => []],
            'record_2' => ['field_name' => []],
            'record_3' => ['field_name' => []]
        ];
        $this->progress->expects($this->once())->method('start')->with('3');
        $this->progress->expects($this->any())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $fields = ['field_name' => []];
        $differentFields = ['field_different' => []];

        $structure = $this->getMockBuilder('Migration\ResourceModel\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])->getMock();
        $structure->expects($this->at(0))->method('getFields')->willReturn($differentFields);
        $structure->expects($this->any())->method('getFields')->willReturn($fields);
        $document = $this->getMockBuilder('Migration\ResourceModel\Document')->disableOriginalConstructor()
            ->setMethods(['getName', 'getRecords', 'getStructure'])
            ->getMock();
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $recordsCollection = $this->getMockBuilder('Migration\ResourceModel\Record\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addRecord'])
            ->getMock();
        $document->expects($this->any())->method('getRecords')->willReturn($recordsCollection);
        $record = $this->getMockBuilder('Migration\ResourceModel\Record')->disableOriginalConstructor()
            ->setMethods(['getFields', 'setValue'])
            ->getMock();
        $record->expects($this->once())->method('getFields')->willReturn(array_keys($fields));
        $record->expects($this->once())->method('setValue')->willReturnSelf();
        $this->recordFactory->expects($this->any())->method('create')->with(['document' => $document])
            ->will($this->returnValue($record));
        $recordsCollection->expects($this->any())->method('addRecord')->with($record);
        $this->source->expects($this->any())->method('getDocument')->willReturn($document);
        $this->source->expects($this->any())->method('getRecords')->willReturnMap(
            [
                ['core_store', 0, null, $recordsData],
                ['core_store_group', 0, null, $recordsData],
                ['core_website', 0, null, $recordsData]

            ]
        );
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);
        $this->destination->expects($this->any())->method('clearDocument')->willReturnSelf();

        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([
                'core_store' => 'store',
                'core_store_group' => 'store_group',
                'core_website' => 'store_website'
            ]);

        $this->data = new Data(
            $this->progress,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->helper
        );
        $this->data->perform();
    }
}
