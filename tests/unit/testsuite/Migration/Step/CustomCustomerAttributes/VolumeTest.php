<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\Logger\Logger;
use Migration\Reader\Map;
use Migration\ResourceModel;

/**
 * Class VolumeTest
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var Volume
     */
    protected $volume;

    /**
     * @var \Migration\Reader\Groups|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerGroups;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->logger = $this->getMock('Migration\Logger\Logger', ['error'], [], '', false);
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->source = $this->getMock(
            'Migration\ResourceModel\Source',
            ['getDocumentList', 'getRecordsCount', 'getDocument'],
            [],
            '',
            false
        );
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getRecordsCount', 'getDocument'],
            [],
            '',
            false
        );

        $this->map = $this->getMockBuilder('Migration\Reader\Map')->disableOriginalConstructor()
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('customer_attr_map_file')->willReturn($this->map);

        $this->readerGroups = $this->getMock('\Migration\Reader\Groups', ['getGroup'], [], '', false);
        $this->readerGroups->expects($this->any())->method('getGroup')->willReturnMap(
            [
                ['source_documents', ['document1' => '']]
            ]
        );

        /** @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject $groupsFactory */
        $groupsFactory = $this->getMock('\Migration\Reader\GroupsFactory', ['create'], [], '', false);
        $groupsFactory->expects($this->any())
            ->method('create')
            ->with('customer_attr_document_groups_file')
            ->willReturn($this->readerGroups);

        $this->volume = new Volume(
            $this->source,
            $this->destination,
            $this->progress,
            $mapFactory,
            $groupsFactory,
            $this->logger
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $fields = ['field1' => []];

        $structure = $this->getMockBuilder('\Migration\ResourceModel\Structure')
            ->disableOriginalConstructor()->setMethods([])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('\Migration\ResourceModel\Document')->disableOriginalConstructor()->getMock();
        $document->expects($this->any())->method('getStructure')->will($this->returnValue($structure));

        $this->map->expects($this->once())->method('getDocumentMap')->with('document1')->willReturn('document2');
        $this->source->expects($this->once())->method('getRecordsCount')->willReturn(3);

        $this->source->expects($this->once())->method('getDocument')->with('document1')->willReturn($document);
        $this->destination->expects($this->once())->method('getDocument')->with('document2')->willReturn($document);


        $this->destination->expects($this->any())->method('getRecordsCount')
            ->willReturnMap([['document2', true, 3]]);
        $this->assertTrue($this->volume->perform());
    }
}
