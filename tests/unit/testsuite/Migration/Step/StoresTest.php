<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\RecordTransformerFactory;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;

/**
 * Class Integrity
 */
class StoresTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Stores
     */
    protected $stores;
    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var RecordTransformerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordTransformerFactory;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->progress = $this->getMockBuilder('Migration\App\ProgressBar\LogLevelProcessor')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->logger = $this->getMockBuilder('Migration\Logger\Logger')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->source = $this->getMockBuilder('Migration\Resource\Source')
            ->disableOriginalConstructor()
            ->getMock();
        $this->destination = $this->getMockBuilder('Migration\Resource\Destination')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->recordTransformerFactory = $this->getMockBuilder('Migration\RecordTransformerFactory')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->recordFactory = $this->getMockBuilder('Migration\Resource\RecordFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }

    public function testIntegrity()
    {
        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()->getMock();

        $this->progress->expects($this->once())->method('start')->with('3');
        $this->progress->expects($this->any())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->source->expects($this->any())->method('getDocument', 'getRecords')->willReturn($document);
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);

        $this->stores = new Stores(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->recordTransformerFactory,
            $this->recordFactory,
            'integrity'
        );
        $this->assertTrue($this->stores->perform());
    }

    public function testData()
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

        $structure = $this->getMockBuilder('Migration\Resource\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])->getMock();
        $structure->expects($this->at(0))->method('getFields')->willReturn($differentFields);
        $structure->expects($this->any())->method('getFields')->willReturn($fields);
        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getName', 'getRecords', 'getStructure'])
            ->getMock();
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $recordsCollection = $this->getMockBuilder('Migration\Resource\Record\Collection')
            ->disableOriginalConstructor()
            ->setMethods(['addRecord'])
            ->getMock();
        $document->expects($this->any())->method('getRecords')->willReturn($recordsCollection);
        $record = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()
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

        $this->stores = new Stores(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->recordTransformerFactory,
            $this->recordFactory,
            'data'
        );
        $this->stores->perform();
    }

    public function testVolumeCheck()
    {
        $fields = ['field_name' => []];

        $structure = $this->getMockBuilder('Migration\Resource\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getStructure'])
            ->getMock();

        $this->progress->expects($this->once())->method('start')->with('3');
        $this->progress->expects($this->any())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $this->source->expects($this->any())->method('getDocument')->willReturn($document);
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);
        $this->source->expects($this->any())->method('getRecordsCount')->with()->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->with()->willReturn(1);

        $this->stores = new Stores(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->recordTransformerFactory,
            $this->recordFactory,
            'volume'
        );
        $this->assertTrue($this->stores->perform());
    }
}
