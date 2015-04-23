<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Reader\Map;
use Migration\Reader\MapInterface;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\App\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
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
    protected $log;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var \Migration\Reader\ListsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerLists;

    public function setUp()
    {
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->source = $this->getMock('\Migration\Resource\Source', ['getDocumentList', 'getDocument'], [], '', false);
        $this->progress = $this->getMock('\Migration\App\ProgressBar', ['start', 'finish', 'advance'], [], '', false);
        $this->destination = $this->getMock(
            '\Migration\Resource\Destination',
            ['getDocumentList', 'getDocument'],
            [],
            '',
            false
        );
        $this->map = $this->getMockBuilder('\Migration\Reader\Map')->disableOriginalConstructor()
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('log_map_file')->willReturn($this->map);

        $this->readerLists = $this->getMock('\Migration\Reader\Lists', ['getList'], [], '', false);
        $this->readerLists->expects($this->any())->method('getList')->willReturnMap(
            [
                ['source_documents', ['document1']],
                ['destination_documents_to_clear', ['document_to_clear']]
            ]
        );

        /** @var \Migration\Reader\ListsFactory|\PHPUnit_Framework_MockObject_MockObject $listsFactory */
        $listsFactory = $this->getMock('\Migration\Reader\ListsFactory', ['create'], [], '', false);
        $listsFactory->expects($this->any())
            ->method('create')
            ->with('log_list_file')
            ->willReturn($this->readerLists);

        $this->log = new Integrity(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory,
            $listsFactory
        );
    }

    /**
     * @covers \Migration\Step\Log\Integrity::getIterationsCount
     */
    public function testPerformMainFlow()
    {
        $fields = ['field1' => []];

        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document1']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document2', 'document_to_clear']));
        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->map->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['document1', MapInterface::TYPE_SOURCE, 'document2'],
                ['document2', MapInterface::TYPE_DEST, 'document1']
            ]
        ) ;

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->map->expects($this->any())->method('getFieldMap')->will($this->returnValue('field1'));
        $this->logger->expects($this->never())->method('error');

        $this->log->perform();
    }
}
