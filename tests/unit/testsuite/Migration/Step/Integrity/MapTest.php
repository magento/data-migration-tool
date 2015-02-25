<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

/**
 * Class IntegrityTest
 */
class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
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
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\MapReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapReader;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    public function setUp()
    {
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->source = $this->getMock('\Migration\Resource\Source', ['getDocumentList', 'getDocument'], [], '', false);
        $this->progress = $this->getMock('\Migration\ProgressBar', ['start', 'finish', 'advance'], [], '', false);
        $this->destination = $this->getMock(
            '\Migration\Resource\Destination',
            ['getDocumentList', 'getDocument'],
            [],
            '',
            false
        );
        $this->mapReader = $this->getMockBuilder('\Migration\MapReader')->disableOriginalConstructor()
            ->setMethods(['getFieldMap', 'getDocumentMap', 'init'])
            ->getMock();
        $this->config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods([])->getMock();
        $this->map = new Map(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->mapReader,
            $this->config
        );
    }

    public function testPerformMainFlow()
    {
        $fields = ['field1' => []];

        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->any())->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->mapReader->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($document));

        $this->mapReader->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));

        $this->logger->expects($this->never())->method('error');

        $this->map->perform();
    }

    public function testPerformDocumentIgnored()
    {
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->mapReader->expects($this->any())->method('getDocumentMap')->will($this->returnValue(false));
        $this->logger->expects($this->never())->method('error');
        $this->map->perform();
    }

    public function testPerformWithDestinationDocMissed()
    {
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue([]));
        $this->mapReader->expects($this->once())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->exactly(1))->method('error')
            ->with(PHP_EOL . 'Next documents from source are not mapped:' . PHP_EOL . 'document');

        $this->map->perform();
    }

    public function testPerformWithSourceDocMissed()
    {
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue([]));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document']));
        $this->mapReader->expects($this->once())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->once())->method('error')
            ->with(PHP_EOL . 'Next documents from destination are not mapped:' . PHP_EOL . 'document');

        $this->map->perform();
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
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->atLeastOnce())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->source->expects($this->exactly(2))->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->exactly(2))->method('getDocument')->with('document')
            ->will($this->returnValue($document));

        $this->mapReader->expects($this->exactly(2))->method('getDocumentMap')->with('document')
            ->will($this->returnArgument(0));
        $this->mapReader->expects($this->at(1))->method('getFieldMap')->with('document', 'field1')
            ->will($this->returnValue('field1'));
        $this->mapReader->expects($this->at(3))->method('getFieldMap')->with('document', 'field2')
            ->will($this->returnValue('field2'));

        $this->logger->expects($this->at(0))->method('error')->with(
            PHP_EOL . 'Next fields from source are not mapped:' .
            PHP_EOL . 'Document name: document; Fields: field1'
        );
        $this->logger->expects($this->at(1))->method('error')->with(
            PHP_EOL . 'Next fields from destination are not mapped:'.
            PHP_EOL . 'Document name: document; Fields: field2'
        );

        $this->map->perform();
    }
}
