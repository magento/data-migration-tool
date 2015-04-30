<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Reader\MapInterface;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
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
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
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

    public function setUp()
    {
        $this->progress = $this->getMockBuilder('\Migration\App\ProgressBar\LogLevelProcessor')->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->logger = $this->getMockBuilder('\Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->source = $this->getMockBuilder('\Migration\Resource\Source')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->destination = $this->getMockBuilder('\Migration\Resource\Destination')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->map = $this->getMockBuilder('\Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap', 'getDocumentList', 'getFieldMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('eav_map_file')->willReturn($this->map);

        $this->readerGroups = $this->getMockBuilder('\Migration\Reader\Groups')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMockBuilder('\Migration\Reader\GroupsFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('eav_document_groups_file')
            ->willReturn($this->readerGroups);
        $this->integrity = new Integrity(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $mapFactory,
            $groupsFactory
        );
    }

    public function testPerformWithoutError()
    {
        $fields = ['field1' => []];
        $this->map->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['document_2', MapInterface::TYPE_SOURCE, 'document_1'],
                ['document_1', MapInterface::TYPE_DEST, 'document_2']
            ]
        ) ;
        $this->map->expects($this->atLeastOnce())->method('getFieldMap')->will($this->returnValue('field1'));

        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('\Migration\Resource\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->any())->method('getDocument')->will($this->returnValue($document));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_2']));

        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_1']));
        $this->destination->expects($this->atLeastOnce())->method('getDocument')->will($this->returnValue($document));

        $this->logger->expects($this->never())->method('error');
        $this->readerGroups->expects($this->any())->method('getGroup')->with('documents')
            ->willReturn(['document_2' => '']);

        $this->assertTrue($this->integrity->perform());
    }

    public function testPerformWithError()
    {
        $fields = ['field1' => []];
        $this->map->expects($this->atLeastOnce())->method('getDocumentMap')->willReturnMap(
            [
                ['document_2', MapInterface::TYPE_SOURCE, 'document_2'],
                ['document_1', MapInterface::TYPE_DEST, 'document_1']
            ]
        ) ;
        $structure = $this->getMockBuilder('\Migration\Resource\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $this->source->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_2']));
        $this->destination->expects($this->atLeastOnce())->method('getDocumentList')
            ->will($this->returnValue(['document_1']));

        $this->logger->expects($this->exactly(2))->method('error');
        $this->readerGroups->expects($this->any())->method('getGroup')->with('documents')->willReturn(['document_2']);

        $this->assertFalse($this->integrity->perform());
    }
}
