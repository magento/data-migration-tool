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
        $this->source = $this->getMock('Migration\Resource\Source', [], [], '', false);
        $this->destination = $this->getMock('Migration\Resource\Destination', [], [], '', false);
        $this->recordFactory = $this->getMock('Migration\Resource\RecordFactory', ['create'], [], '', false);
        $this->handlerManagerFactory = $this->getMock('Migration\Handler\ManagerFactory', ['create'], [], '', false);
        $this->mapReader = $this->getMock('Migration\MapReader', [], [], '', false);
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
}
