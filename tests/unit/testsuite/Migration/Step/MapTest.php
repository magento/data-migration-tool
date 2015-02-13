<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Handler;
use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

class MapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Progress|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var MapReader
     */
    protected $mapReader;

    /**
     * @var Handler\ManagerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerManagerFactory;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapStep;

    public function setUp()
    {
        $this->progress = $this->getMock('Migration\Step\Progress', [], [], '', false);
        $this->logger = $this->getMock('Migration\Logger\Logger', [], [], '', false);
        $this->source = $this->getMock(
            'Migration\Resource\Source',
            ['getDocument', 'getDocumentList', 'getRecords'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\Resource\Destination',
            ['getDocument', 'saveRecords'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('Migration\Resource\RecordFactory', ['create'], [], '', false);
        $this->handlerManagerFactory = $this->getMock('Migration\Handler\ManagerFactory', ['create'], [], '', false);
        $this->mapReader = $this->getMock('Migration\MapReader', ['getDocumentMap', 'init'], [], '', false);
        $this->mapReader->expects($this->once())->method('init');
        $this->mapStep = new Map(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->handlerManagerFactory,
            $this->mapReader
        );
    }

    public function testGetMaxSteps()
    {
        $stepsArray = array_fill(1, 10, 'dummy');
        $this->source->expects($this->once())->method('getDocumentList')->will($this->returnValue($stepsArray));
        $this->assertEquals(10, $this->mapStep->getMaxSteps());
    }

    public function testGetMapEmptyDestinationDocumentName()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $this->mapStep->run();
    }

    public function testGetMapEmptyDestinationDocument()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $dstDocName = 'config_data';
        $this->mapReader->expects($this->once())->method('getDocumentMap')->will($this->returnValue($dstDocName));
        $this->destination->expects($this->once())->method('getDocument')->will($this->returnValue(false));
        $this->setExpectedException('Exception');
        $this->mapStep->run();
    }

    public function testGetMap()
    {
        $sourceDocName = 'core_config_data';
        $this->source->expects($this->once())->method('getDocumentList')->will($this->returnValue([$sourceDocName]));
        $dstDocName = 'config_data';
        $this->mapReader->expects($this->once())->method('getDocumentMap')->will($this->returnValue($dstDocName));
        $sourceDocument = $this->getMock('\Migration\Resource\Document', ['getRecords'], [], '', false);
        $this->source->expects($this->once())->method('getDocument')->will(
            $this->returnValue($sourceDocument)
        );
        $destinationDocument = $this->getMock('\Migration\Resource\Document', [], [], '', false);
        $this->destination->expects($this->once())->method('getDocument')->will(
            $this->returnValue($destinationDocument)
        );
        $handlerManager = $destinationDocument = $this->getMock(
            '\Migration\Handler\Manager',
            ['init', 'process'],
            [],
            '',
            false
        );
        $this->handlerManagerFactory->expects($this->once())->method('create')->will(
            $this->returnValue($handlerManager)
        );
        $handlerManager->expects($this->once())->method('init');

        $bulk = [['id' => 4, 'name' => 'john']];
        $this->source->expects($this->at(2))->method('getRecords')->will($this->returnValue($bulk));
        $this->source->expects($this->at(3))->method('getRecords')->will($this->returnValue([]));
        $recordCollection = $this->getMock('\Migration\Resource\Record\Collection', ['addRecord'], [], '', false);
        $sourceDocument->expects($this->once())->method('getRecords')->will($this->returnValue($recordCollection));
        $record = $this->getMock('\Migration\Resource\Record', [], [], '', false);
        $this->recordFactory->expects($this->once())->method('create')->will($this->returnValue($record));
        $recordCollection->expects($this->once())->method('addRecord')->with($record);
        $destCollection =  $this->getMock('\Migration\Resource\Record\Collection', [], [], '', false);
        $handlerManager->expects($this->once())->method('process')->with($recordCollection)->will(
            $this->returnValue($destCollection)
        );
        $this->destination->expects($this->once())->method('saveRecords')->with($dstDocName, $destCollection);
        $this->mapStep->run();
    }
}
