<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

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
        $this->integrity = new Integrity($this->progress, $this->logger, $this->source, $this->destination);
    }

    public function testRun()
    {
        $documentListSource = ['doc1', 'doc2'];
        $documentListDestination = ['doc1', 'doc3'];
        $documentSourceStructure = ['field1' => [], 'field2' => []];
        $documentDestinationStructure = ['field1' => [], 'field3' => []];
        $this->progress->expects($this->any())->method('getProgress')->will($this->returnValue(0));
        $this->progress
            ->expects($this->any())
            ->method('getMaxSteps')
            ->will($this->returnValue(count($documentListSource)));
        $this->source->expects($this->any())->method('getDocumentList')->will($this->returnValue($documentListSource));
        $this->destination
            ->expects($this->any())
            ->method('getDocumentList')
            ->will($this->returnValue($documentListDestination));

        $sourceValueMap = [];
        $destinationValueMap = [];
        $i = 0;
        foreach ($documentListSource as $i => $document) {
            $sourceDocument = $this->getMock(
                '\Migration\Resource\Document\Document',
                ['getName', 'getStructure'],
                [],
                '',
                false
            );
            $sourceStructure = $this->getMock('\Migration\Resource\Document\Structure', ['getFields'], [], '', false);
            $sourceStructure->expects($this->any())
                ->method('getFields')
                ->will($this->returnValue($documentSourceStructure));
            $sourceDocument->expects($this->any())->method('getStructure')->will($this->returnValue($sourceStructure));
            $sourceDocument->expects($this->any())->method('getName')->will($this->returnValue($document));
            if (in_array($document, $documentListDestination)) {
                $destinationDocument = $this->getMock(
                    '\Migration\Resource\Document\Document',
                    ['getName', 'getStructure'],
                    [],
                    '',
                    false
                );
                $destinationStructure = $this->getMock(
                    '\Migration\Resource\Document\Structure',
                    ['getFields'],
                    [],
                    '',
                    false
                );
                $destinationStructure->expects($this->any())
                    ->method('getFields')
                    ->will($this->returnValue($documentDestinationStructure));
                $destinationDocument->expects($this->any())
                    ->method('getStructure')
                    ->will($this->returnValue($destinationStructure));
                $destinationDocument->expects($this->any())->method('getName')->will($this->returnValue($document));
            } else {
                $destinationDocument = false;
            }
            $sourceValueMap[] = [$document, $sourceDocument];
            $destinationValueMap[] = [$document, $destinationDocument];
            $this->logger
                ->expects($this->at($i))
                ->method('debug')
                ->with($this->equalTo("Integrity check of {$document}"));
        }
        $this->source->method('getDocument')->will($this->returnValueMap($sourceValueMap));
        $this->destination->method('getDocument')->will($this->returnValueMap($destinationValueMap));

        $error = "The documents bellow are not exist in the destination resource:\ndoc2\n";
        $this->logger->expects($this->at(++$i))->method('error')->with($this->equalTo($error));
        $error = "The documents bellow are not exist in the source resource:\ndoc3\n";
        $this->logger->expects($this->at(++$i))->method('error')->with($this->equalTo($error));
        $error = "In the documents bellow fields are not exist in the destination resource:"
            . "\nDocument name:doc1; Fields:field2\n";
        $this->logger->expects($this->at(++$i))->method('error')->with($this->equalTo($error));
        $error = "In the documents bellow fields are not exist in the source resource:"
            . "\nDocument name:doc1; Fields:field3\n";
        $this->logger->expects($this->at(++$i))->method('error')->with($this->equalTo($error));
        $this->integrity->run();
    }
}
