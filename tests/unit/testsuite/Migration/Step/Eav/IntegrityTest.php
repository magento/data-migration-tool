<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Reader\MapInterface;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\Step\Eav\Integrity|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $integrity;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @var \Migration\Reader\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\Step\Eav\Integrity\AttributeGroupNames|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeGroupNames;

    /**
     * @var \Migration\Step\Eav\Integrity\AttributeFrontendInput|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeFrontendInput;

    /**
     * @var \Migration\Step\Eav\Integrity\ClassMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMapIntegrity;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->getMockBuilder(\Migration\App\ProgressBar\LogLevelProcessor::class)
            ->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->logger = $this->getMockBuilder(\Migration\Logger\Logger::class)->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'getDocumentList', 'getFieldMap', 'isDocumentIgnored'])
            ->getMock();
        $this->attributeGroupNames = $this->getMockBuilder(\Migration\Step\Eav\Integrity\AttributeGroupNames::class)
            ->setMethods(['checkAttributeGroupNames'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeGroupNames->expects($this->once())
            ->method('checkAttributeGroupNames')
            ->willReturn([]);
        $this->attributeFrontendInput =
            $this->getMockBuilder(\Migration\Step\Eav\Integrity\AttributeFrontendInput::class)
            ->setMethods(['checkAttributeFrontendInput'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeFrontendInput->expects($this->once())
            ->method('checkAttributeFrontendInput')
            ->willReturn([]);
        $this->classMapIntegrity =
            $this->getMockBuilder(\Migration\Step\Eav\Integrity\ClassMap::class)
            ->setMethods(['checkClassMapping'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->classMapIntegrity->expects($this->once())
            ->method('checkClassMapping')
            ->willReturn([]);

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('eav_map_file')->willReturn($this->map);

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
        $config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()
            ->getMock();
        $this->integrity = new Integrity(
            $this->progress,
            $this->logger,
            $config,
            $this->source,
            $this->destination,
            $mapFactory,
            $groupsFactory,
            $this->attributeGroupNames,
            $this->attributeFrontendInput,
            $this->classMapIntegrity
        );
    }

    /**
     * @return void
     */
    public function testPerformWithoutError()
    {
        $fields = ['field1' => ['DATA_TYPE' => 'int']];
        $this->map->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['source_document', MapInterface::TYPE_SOURCE, 'destination_document'],
                ['destination_document', MapInterface::TYPE_DEST, 'source_document']
            ]
        ) ;
        $this->map->expects($this->atLeastOnce())->method('getFieldMap')->willReturn('field1');
        $this->map->expects($this->atLeastOnce())->method('isDocumentIgnored')->willReturn(false);

        $structure = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['source_document']));

        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['destination_document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocument')->will($this->returnValue($document));

        $this->logger->expects($this->never())->method('addRecord');
        $this->readerGroups->expects($this->any())->method('getGroup')->with('documents')
            ->willReturn(['source_document' => 0]);

        $this->assertTrue($this->integrity->perform());
    }

    /**
     * @return void
     */
    public function testPerformWithError()
    {
        $fields = ['field1' => ['DATA_TYPE' => 'int']];
        $this->map->expects($this->atLeastOnce())->method('getDocumentMap')->willReturnMap(
            [
                ['source_document', MapInterface::TYPE_SOURCE, 'source_document'],
                ['common_document', MapInterface::TYPE_SOURCE, 'common_document'],
                ['source_document', MapInterface::TYPE_DEST, 'source_document'],
                ['common_document', MapInterface::TYPE_DEST, 'common_document'],
            ]
        ) ;
        $structure = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['source_document', 'common_document']));
        $this->source->expects($this->atLeastOnce())->method('getDocument')->willReturn($document);

        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['common_document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocument')->willReturn($document);

        $this->logger->expects($this->once())->method('addRecord')
            ->with(400, 'Source documents are not mapped: source_document');
        $this->readerGroups->expects($this->any())->method('getGroup')->with('documents')
            ->willReturn(['source_document' => 0, 'common_document' => 1]);

        $this->assertFalse($this->integrity->perform());
    }
}
