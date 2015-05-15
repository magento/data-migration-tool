<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
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
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Integrity
     */
    protected $integrity;

    /**
     * @var \Migration\Reader\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    public function setUp()
    {
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->source = $this->getMock('\Migration\Resource\Source', ['getDocumentList', 'getDocument'], [], '', false);
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            '\Migration\Resource\Destination',
            ['getDocumentList', 'getDocument'],
            [],
            '',
            false
        );
        $this->map = $this->getMockBuilder('\Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods(['getFieldMap', 'getDocumentMap', 'init', 'isDocumentIgnored'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        $this->integrity = new Integrity(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory
        );
    }

    public function testPerformMainFlow()
    {
        $this->setupFieldsValidation();

        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);

        $this->map->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));

        $this->logger->expects($this->never())->method('error');

        $this->integrity->perform();
    }

    public function testPerformDocumentIgnored()
    {
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnValue(false));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(true);
        $this->logger->expects($this->never())->method('error');
        $this->integrity->perform();
    }

    public function testPerformWithDestinationDocMissed()
    {
        $this->setupFieldsValidation();
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1', 'document2']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->map->expects($this->any())->method('isDocumentIgnored')->willReturn(false);
        $this->logger->expects($this->exactly(1))->method('error')
            ->with('Source documents not mapped: document1');

        $this->integrity->perform();
    }

    public function testPerformWithSourceDocMissed()
    {
        $this->setupFieldsValidation();
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->willReturn(['document2']);
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1', 'document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->once())->method('error')
            ->with('Destination documents not mapped: document1');

        $this->integrity->perform();
    }

    public function testPerformWithDocNotExists()
    {
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->any())->method('error')
            ->with('Mapped documents not found. Check your configuration.');

        $this->integrity->perform();
    }

    public function testPerformWithSourceFieldErrors()
    {
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $structure->expects($this->at(0))->method('getFields')->will($this->returnValue(['field1' => []]));
        $structure->expects($this->at(1))->method('getFields')->will($this->returnValue(['field2' => []]));
        $structure->expects($this->at(2))->method('getFields')->will($this->returnValue(['field2' => []]));
        $structure->expects($this->at(3))->method('getFields')->will($this->returnValue(['field1' => []]));

        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
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

        $this->logger->expects($this->at(0))->method('error')->with(
            'Source fields not mapped. Document: document. Fields: field1'
        );
        $this->logger->expects($this->at(1))->method('error')->with(
            'Destination fields not mapped. Document: document. Fields: field2'
        );

        $this->integrity->perform();
    }

    protected function setupFieldsValidation()
    {
        $fields = ['field1' => []];

        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($document));
    }
}
