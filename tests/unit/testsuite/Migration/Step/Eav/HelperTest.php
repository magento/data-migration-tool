<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Reader\Map;
use Migration\Reader\MapInterface;
use Migration\RecordTransformerFactory;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

/**
 * Class HelperTest
 */
class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var RecordTransformerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->map = $this->getMockBuilder('Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('eav_map_file')->willReturn($this->map);

        $this->source = $this->getMockBuilder('Migration\ResourceModel\Source')->disableOriginalConstructor()
            ->setMethods(['getRecordsCount', 'getRecords'])
            ->getMock();
        $this->destination = $this->getMockBuilder('Migration\ResourceModel\Destination')->disableOriginalConstructor()
            ->setMethods(['getRecordsCount', 'getRecords'])
            ->getMock();
        $this->factory = $this->getMockBuilder('Migration\RecordTransformerFactory')->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->helper = new Helper($mapFactory, $this->source, $this->destination, $this->factory);
    }

    /**
     * @return void
     */
    public function testGetSourceRecordsCount()
    {
        $this->source->expects($this->once())->method('getRecordsCount')->with('some_document')
            ->will($this->returnValue(5));
        $this->assertEquals(5, $this->helper->getSourceRecordsCount('some_document'));
    }

    /**
     * @return void
     */
    public function testGetDestinationRecordsCount()
    {
        $this->map->expects($this->once())->method('getDocumentMap')
            ->with('some_document', MapInterface::TYPE_SOURCE)
            ->will($this->returnValue('some_dest_document'));
        $this->destination->expects($this->once())->method('getRecordsCount')->with('some_dest_document')
            ->will($this->returnValue(5));
        $this->assertEquals(5, $this->helper->getDestinationRecordsCount('some_document'));
    }

    /**
     * @return void
     */
    public function testGetSourceRecords()
    {
        $this->source->expects($this->once())->method('getRecordsCount')->will($this->returnValue(1));
        $this->source->expects($this->once())->method('getRecords')->with('test_source_document', 0, 1)
            ->will($this->returnValue([['key' => 'key_value', 'field' => 'field_value']]));

        $result = [
            'key_value-field_value' => ['key' => 'key_value', 'field' => 'field_value']
        ];

        $this->assertEquals($result, $this->helper->getSourceRecords('test_source_document', ['key', 'field']));
    }

    /**
     * @return void
     */
    public function testGetDestinationRecords()
    {
        $this->map->expects($this->once())->method('getDocumentMap')
            ->with('test_source_document', MapInterface::TYPE_SOURCE)
            ->will($this->returnValue('test_dest_document'));
        $this->destination->expects($this->once())->method('getRecordsCount')->will($this->returnValue(1));
        $this->destination->expects($this->once())->method('getRecords')->with('test_dest_document', 0, 1)
            ->will($this->returnValue([['key' => 'key_value', 'field' => 'field_value']]));

        $result = [
            'key_value-field_value' => ['key' => 'key_value', 'field' => 'field_value']
        ];
        $this->assertEquals($result, $this->helper->getDestinationRecords('test_source_document', ['key', 'field']));
    }

    /**
     * @return void
     */
    public function testGetSourceRecordsNoKey()
    {
        $row = ['key' => 'key_value', 'field' => 'field_value'];
        $this->source->expects($this->once())->method('getRecordsCount')->will($this->returnValue(1));
        $this->source->expects($this->once())->method('getRecords')->with('test_source_document', 0, 1)
            ->will($this->returnValue([$row]));

        $this->assertEquals([$row], $this->helper->getSourceRecords('test_source_document'));
    }

    /**
     * @return void
     */
    public function testGetDestinationRecordsNoKey()
    {
        $row = ['key' => 'key_value', 'field' => 'field_value'];
        $this->map->expects($this->once())->method('getDocumentMap')
            ->with('test_source_document', MapInterface::TYPE_SOURCE)
            ->will($this->returnValue('test_dest_document'));
        $this->destination->expects($this->once())->method('getRecordsCount')->will($this->returnValue(1));
        $this->destination->expects($this->once())->method('getRecords')->with('test_dest_document', 0, 1)
            ->will($this->returnValue([$row]));

        $this->assertEquals([$row], $this->helper->getDestinationRecords('test_source_document'));
    }

    /**
     * @return void
     */
    public function testGetRecordTransformer()
    {
        $sourceDocument = $this->getMockBuilder('Migration\ResourceModel\Document')->disableOriginalConstructor()
            ->getMock();
        $destinationDocument = $this->getMockBuilder('Migration\ResourceModel\Document')->disableOriginalConstructor()
            ->getMock();
        $recordTransformer = $this->getMockBuilder('Migration\RecordTransformer')->disableOriginalConstructor()
            ->setMethods(['init'])
            ->getMock();

        $this->factory->expects($this->once())->method('create')
            ->with(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destinationDocument,
                    'mapReader' => $this->map
                ]
            )->will($this->returnValue($recordTransformer));

        $recordTransformer->expects($this->once())->method('init')->will($this->returnSelf());

        $this->assertSame(
            $recordTransformer,
            $this->helper->getRecordTransformer($sourceDocument, $destinationDocument)
        );
    }
}
