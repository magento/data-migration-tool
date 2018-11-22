<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentSource;

    /**
     * @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $documentDestination;

    /**
     * @var Integrity
     */
    protected $integrity;

    /**
     * @var \Migration\Reader\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['debug', 'addRecord', 'warning']
        );
        $this->source = $this->createPartialMock(
            \Migration\ResourceModel\Source::class,
            ['getDocumentList', 'getDocument']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance']
        );
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getDocumentList', 'getDocument']
        );
        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods(['getFieldMap', 'getDocumentMap', 'init', 'isDocumentIgnored', 'isFieldDataTypeIgnored'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()->getMock();

        $this->integrity = new Integrity(
            $this->progress,
            $this->logger,
            $config,
            $this->source,
            $this->destination,
            $mapFactory
        );
    }

    /**
     * @return void
     */
    public function testPerformMainFlow()
    {
        $this->setupFieldsValidation();

        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);

        $this->map->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));

        $this->logger->expects($this->never())->method('addRecord');

        $this->integrity->perform();
    }

    /**
     * @return void
     */
    public function testPerformDocumentIgnored()
    {
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnValue(false));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(true);
        $this->logger->expects($this->never())->method('addRecord');
        $this->integrity->perform();
    }

    /**
     * @return void
     */
    public function testPerformWithDestinationDocMissed()
    {
        $this->setupFieldsValidation();
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1', 'document2']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);
        $this->logger->expects($this->exactly(1))->method('addRecord')
            ->with(400, 'Source documents are not mapped: document1');

        $this->integrity->perform();
    }

    /**
     * @return void
     */
    public function testPerformWithSourceDocMissed()
    {
        $this->setupFieldsValidation();
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->willReturn(['document2']);
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1', 'document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->once())->method('addRecord')
            ->with(400, 'Destination documents are not mapped: document1');

        $this->integrity->perform();
    }

    /**
     * @return void
     */
    public function testPerformWithSourceFieldErrors()
    {
        $structure = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $structure->expects($this->at(0))->method('getFields')->will($this->returnValue(['field1' => []]));
        $structure->expects($this->at(1))->method('getFields')->will($this->returnValue(['field2' => []]));
        $structure->expects($this->at(2))->method('getFields')->will($this->returnValue(['field2' => []]));
        $structure->expects($this->at(3))->method('getFields')->will($this->returnValue(['field1' => []]));

        $document = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()
            ->getMock();
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $document->expects($this->any())->method('getName')->willReturn('document');

        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->with('document')
            ->will($this->returnValue($document));

        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);
        $this->map->expects($this->exactly(2))->method('getDocumentMap')->with('document')
            ->will($this->returnArgument(0));
        $this->map->expects($this->at(2))->method('getFieldMap')->with('document', 'field1')
            ->will($this->returnValue('field1'));
        $this->map->expects($this->at(5))->method('getFieldMap')->with('document', 'field2')
            ->will($this->returnValue('field2'));

        $this->logger->expects($this->at(0))->method('addRecord')->with(
            400,
            'Source fields are not mapped. Document: document. Fields: field1'
        );
        $this->logger->expects($this->at(1))->method('addRecord')->with(
            400,
            'Destination fields are not mapped. Document: document. Fields: field2'
        );

        $this->integrity->perform();
    }

    /**
     * @return void
     */
    public function testPerformWithMismatchDocumentFieldDataTypes()
    {
        $this->setupFieldsValidation(true);
        $this->documentSource->expects($this->any())->method('getName')->will($this->returnValue('document1'));
        $this->documentDestination->expects($this->any())->method('getName')->will($this->returnValue('document1'));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);
        $this->map->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));
        $this->map->expects($this->any())->method('isFieldDataTypeIgnored')->will($this->returnValue(false));
        $this->logger->expects($this->at(0))->method('warning')
            ->with('Mismatch of data types. Source document: document1. Fields: field1');
        $this->logger->expects($this->at(1))->method('warning')
            ->with('Mismatch of data types. Destination document: document1. Fields: field1');

        $this->integrity->perform();
    }

    /**
     * @param bool|false $dataTypeMismatch
     * @return void
     */
    protected function setupFieldsValidation($dataTypeMismatch = false)
    {
        $dataTypeSource = $dataTypeMismatch ? 'varchar' : 'int';
        $fieldsSource = ['field1' => ['DATA_TYPE' => $dataTypeSource]];
        $structureSource = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structureSource->expects($this->any())->method('getFields')->will($this->returnValue($fieldsSource));
        $this->documentSource = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()->getMock();
        $this->documentSource->expects($this->any())->method('getStructure')
            ->will($this->returnValue($structureSource));

        $dataTypeDestination = 'int';
        $fieldsDestination = ['field1' => ['DATA_TYPE' => $dataTypeDestination]];
        $structureDestination = $this->getMockBuilder(\Migration\ResourceModel\Structure::class)
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structureDestination->expects($this->any())->method('getFields')->will($this->returnValue($fieldsDestination));
        $this->documentDestination = $this->getMockBuilder(\Migration\ResourceModel\Document::class)
            ->disableOriginalConstructor()->getMock();
        $this->documentDestination->expects($this->any())->method('getStructure')
            ->will($this->returnValue($structureDestination));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($this->documentSource));
        $this->destination->expects($this->any())->method('getDocument')
            ->will($this->returnValue($this->documentDestination));
    }
}
