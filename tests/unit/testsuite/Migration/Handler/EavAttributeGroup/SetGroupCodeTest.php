<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttributeGroup;

use Migration\ResourceModel\Record;
use Migration\Step\DatabaseStage;

/**
 * Class SetGroupCodeTest
 */
class SetGroupCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->at(1))->method('getValue')->with('attribute_set_id')->willReturn(1);
        $recordToHandle->expects($this->at(2))->method('getValue')->with('attribute_group_name')
            ->willReturn('Migration General');
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, 'product-details');

        $config = $this->getMockBuilder('Migration\Config')
            ->disableOriginalConstructor()->setMethods(['getSource'])->getMock();
        $source = $this->getMockBuilder('Migration\ResourceModel\Source')
            ->disableOriginalConstructor()->setMethods(['getAdapter', 'addDocumentPrefix'])->getMock();
        $adapter = $this->getMockBuilder('Migration\ResourceModel\Adapter\Mysql')
            ->disableOriginalConstructor()->setMethods(['fetchCol', 'getSelect'])->getMock();

        $config->expects($this->once())->method('getSource')->willReturn(['type' => DatabaseStage::SOURCE_TYPE]);

        $source->expects($this->any())->method('addDocumentPrefix')->willReturn($this->returnArgument(1));
        $source->expects($this->once())->method('getAdapter')->willReturn($adapter);

        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()->setMethods(['from', 'join', 'where', 'getAdapter'])->getMock();
        $select->expects($this->once())->method('from')->willReturnSelf();
        $select->expects($this->once())->method('join')->willReturnSelf();
        $select->expects($this->once())->method('where')->willReturnSelf();
        $select->expects($this->once())->method('getAdapter')->willReturn($adapter);

        $adapter->expects($this->once())->method('getSelect')->willReturn($select);
        $adapter->expects($this->once())->method('fetchCol')->willReturn([1=>0, 2=>1]);

        $handler = new SetGroupCode($config, $source);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
