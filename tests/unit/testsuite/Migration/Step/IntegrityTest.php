<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\MapReader;

/**
 * Class ProgressTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Progress|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Migration\Step\Integrity
     */
    protected $integrity;

    /**
     * @var \Migration\MapReader
     */
    protected $map;

    public function setUp()
    {
        $this->progress = $this->getMock(
            '\Migration\Step\Progress',
            ['getProgress', 'getMaxSteps', 'advance', 'finish', 'setStep', 'fail'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->source = $this->getMock('\Migration\Resource\Source', ['getDocumentList', 'getDocument'], [], '', false);
        $this->destination = $this->getMock(
            '\Migration\Resource\Destination',
            ['getDocumentList', 'getDocument'],
            [],
            '',
            false
        );
        $this->map = $this->getMockBuilder('\Migration\MapReader')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->integrity = new Integrity($this->progress, $this->logger, $this->source, $this->destination, $this->map);
    }

    public function testRunMainFlow()
    {
        $fields = ['field1' => []];

        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->progress->expects($this->once())->method('setStep')->with($this->integrity);

        $this->source->expects($this->exactly(2))->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->exactly(2))->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnArgument(0));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($document));

        $this->map->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));

        $this->logger->expects($this->never())->method('error');

        $this->integrity->run();
    }

    public function testRunDocumentIgnored()
    {
        $this->source->expects($this->exactly(2))->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->exactly(2))->method('getDocumentList')
            ->will($this->returnValue(['document2']));
        $this->map->expects($this->any())->method('getDocumentMap')->will($this->returnValue(false));
        $this->logger->expects($this->never())->method('error');
        $this->integrity->run();
    }

    public function testRunWithDestinationDocMissed()
    {
        $this->source->expects($this->exactly(2))->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->exactly(2))->method('getDocumentList')->will($this->returnValue([]));
        $this->map->expects($this->once())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->exactly(1))->method('error')
            ->with("Next documents from source are not mapped:\ndocument\n");

        $this->integrity->run();
    }

    public function testRunWithSourceDocMissed()
    {
        $this->source->expects($this->exactly(2))->method('getDocumentList')->will($this->returnValue([]));
        $this->destination->expects($this->exactly(2))->method('getDocumentList')
            ->will($this->returnValue(['document']));
        $this->map->expects($this->once())->method('getDocumentMap')->will($this->returnArgument(0));
        $this->logger->expects($this->once())->method('error')
            ->with("Next documents from destination are not mapped:\ndocument\n");

        $this->integrity->run();
    }

    public function testRunWithSourceFieldErrors()
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

        $this->source->expects($this->exactly(2))->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->exactly(2))->method('getDocumentList')
            ->will($this->returnValue(['document']));

        $this->source->expects($this->exactly(2))->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->exactly(2))->method('getDocument')->with('document')
            ->will($this->returnValue($document));

        $this->map->expects($this->exactly(2))->method('getDocumentMap')->with('document')
            ->will($this->returnArgument(0));
        $this->map->expects($this->at(1))->method('getFieldMap')->with('document', 'field1')
            ->will($this->returnValue('field1'));
        $this->map->expects($this->at(3))->method('getFieldMap')->with('document', 'field2')
            ->will($this->returnValue('field2'));

        $this->logger->expects($this->at(0))->method('error')
            ->with("Next fields from source are not mapped:\nDocument name:document; Fields:field1\n");
        $this->logger->expects($this->at(1))->method('error')
            ->with("Next fields from destination are not mapped:\nDocument name:document; Fields:field2\n");

        $this->integrity->run();
    }

    public function testGetMaxSteps()
    {
        $this->source->expects($this->once())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->destination->expects($this->once())->method('getDocumentList')->will($this->returnValue(['document']));
        $this->assertEquals(2, $this->integrity->getMaxSteps());
    }
}
