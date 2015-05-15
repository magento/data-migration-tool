<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Reader\Map;
use Migration\Reader\MapInterface;
use Migration\Resource\Record;
use Migration\Resource\Source;

/**
 * Class ConditionSqlTest
 */
class ConditionSqlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConditionSql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handler;

    /**
     * @var Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /** @var  Map|\PHPUnit_Framework_MockObject_MockObject */
    protected $map;

    public function setUp()
    {
        /** @var Map|\PHPUnit_Framework_MockObject_MockObject $map */
        $this->map = $this->getMockBuilder('Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods(['getDocumentMap'])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('map_file')->willReturn($this->map);

        /** @var Source|\PHPUnit_Framework_MockObject_MockObject $source */
        $this->source = $this->getMockBuilder('Migration\Resource\Source')->disableOriginalConstructor()
            ->setMethods(['getDocumentList', 'addDocumentPrefix'])
            ->getMock();
        /** @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject $destination */
        $destination = $this->getMockBuilder('Migration\Resource\Destination')->disableOriginalConstructor()
            ->setMethods(['addDocumentPrefix'])
            ->getMock();
        $destination->expects($this->any())->method('addDocumentPrefix')->will($this->returnCallback(function ($value) {
            return 'pfx_' . $value;
        }));

        $this->handler = new ConditionSql($mapFactory, $this->source, $destination);
    }

    public function testHandle()
    {
        /** @var Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\Resource\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder('Migration\Resource\Record')->disableOriginalConstructor()->getMock();

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with($fieldName)
            ->will($this->returnValue('SELECT * FROM `source_some_document` LEFT JOIN `source_other_document`'));
        $recordToHandle->expects($this->once())->method('setValue')
            ->with($fieldName, 'SELECT * FROM `pfx_dest_some_document` LEFT JOIN `pfx_dest_other_document`');

        $this->map->expects($this->any())->method('getDocumentMap')->willReturnMap(
            [
                ['source_some_document', MapInterface::TYPE_SOURCE, 'dest_some_document'],
                ['source_other_document', MapInterface::TYPE_SOURCE, 'dest_other_document'],
                ['source_ignored_document', MapInterface::TYPE_SOURCE, false]
            ]
        );

        $this->source->expects($this->once())->method('getDocumentList')
            ->will($this->returnValue(['source_some_document', 'source_other_document', 'source_ignored_document']));
        $this->source->expects($this->any())->method('addDocumentPrefix')->will($this->returnArgument(0));

        $this->handler->setField($fieldName);
        $this->handler->handle($recordToHandle, $oppositeRecord);
    }
}
