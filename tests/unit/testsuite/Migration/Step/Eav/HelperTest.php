<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class HelperTest extends \PHPUnit\Framework\TestCase
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
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerAttributes;

    /**
     * @var \Migration\ResourceModel\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pdoMysql;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('eav_map_file')->willReturn($this->map);

        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)->disableOriginalConstructor()
            ->setMethods(['getRecordsCount', 'getRecords', 'getAdapter', 'addDocumentPrefix'])
            ->getMock();
        $this->destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRecordsCount', 'getRecords', 'deleteDocumentBackup'])
            ->getMock();
        $this->factory = $this->getMockBuilder(\Migration\RecordTransformerFactory::class)->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->readerGroups = $this->getMockBuilder(\Migration\Reader\Groups::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->readerAttributes = $this->getMockBuilder(\Migration\Reader\Groups::class)
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
            ->willReturnMap(
                [
                    ['eav_document_groups_file', $this->readerGroups],
                    ['eav_attribute_groups_file', $this->readerAttributes]
                ]
            );
        $this->adapter = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Mysql::class,
            ['getSelect']
        );
        $this->pdoMysql = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            ['fetchPairs']
        );
        $this->select = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['from', 'getAdapter']
        );
        $this->helper = new Helper($mapFactory, $this->source, $this->destination, $this->factory, $groupsFactory);
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
        $sourceDocument = $this->getMockBuilder(\Migration\ResourceModel\Document::class)->disableOriginalConstructor()
            ->getMock();
        $destinationDocument = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $recordTransformer = $this->getMockBuilder(\Migration\RecordTransformer::class)->disableOriginalConstructor()
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

    /**
     * @return void
     */
    public function testDeleteBackups()
    {
        $this->readerGroups->expects($this->once())->method('getGroup')->with('documents')
            ->willReturn(['some_document' => 0]);
        $this->map->expects($this->once())->method('getDocumentMap')
            ->with('some_document', MapInterface::TYPE_SOURCE)
            ->will($this->returnValue('some_dest_document'));
        $this->destination->expects($this->once())->method('deleteDocumentBackup')->with('some_dest_document');
        $this->helper->deleteBackups();
    }

    /**
     * @return void
     */
    public function testClearIgnoredAttributes()
    {
        $allSourceRecords = [
            0 => [
                'attribute_code' => 'ignored_attribute',
                'entity_type_id' => 111,
            ],
            1 => [
                'attribute_code' => 'attribute_1',
                'entity_type_id' => 1,
            ],
            2 => [
                'attribute_code' => 'attribute_2',
                'entity_type_id' => 2,
            ]
        ];
        $clearedSourceRecords = [
            1 => [
                'attribute_code' => 'attribute_1',
                'entity_type_id' => 1,
            ],
            2 => [
                'attribute_code' => 'attribute_2',
                'entity_type_id' => 2,
            ]
        ];
        $entityTypesCodeToId = [
            'ignored_attribute_type' => 111,
            'attribute_type_1' => 1,
            'attribute_type_2' => 2,
        ];
        $eavEntityTypeTable = 'eav_entity_type';
        $this->readerAttributes->expects($this->once())->method('getGroup')->with('ignore')
            ->willReturn(['ignored_attribute' => ['ignored_attribute_type']]);
        $this->source->expects($this->once())->method('getAdapter')->willReturn($this->adapter);
        $this->source
            ->expects($this->once())
            ->method('addDocumentPrefix')
            ->with($eavEntityTypeTable)
            ->willReturn($eavEntityTypeTable);
        $this->adapter->expects($this->once())->method('getSelect')->willReturn($this->select);
        $this->select
            ->expects($this->once())
            ->method('from')
            ->with($eavEntityTypeTable, ['entity_type_code', 'entity_type_id'])
            ->will($this->returnSelf());
        $this->select
            ->expects($this->once())
            ->method('getAdapter')
            ->will($this->returnValue($this->pdoMysql));
        $this->pdoMysql
            ->expects($this->once())
            ->method('fetchPairs')
            ->with($this->select)
            ->will($this->returnValue($entityTypesCodeToId));
        $this->assertEquals($clearedSourceRecords, $this->helper->clearIgnoredAttributes($allSourceRecords));
    }
}
