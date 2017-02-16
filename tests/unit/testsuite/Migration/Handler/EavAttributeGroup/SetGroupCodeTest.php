<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttributeGroup;

use Migration\ResourceModel\Record;
use Migration\Step\DatabaseStage;
use Migration\Model\Eav\AttributeGroupNameToCodeMap;

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
        $groupName = 'Migration General';
        $groupCode = 'migration-general';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder('Migration\ResourceModel\Record')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AttributeGroupNameToCodeMap $attributeGroupNameToCodeMap|\PHPUnit_Framework_MockObject_MockObject */
        $attributeGroupNameToCodeMap = $this->getMockBuilder('Migration\Model\Eav\AttributeGroupNameToCodeMap')
            ->setMethods(['getGroupCodeMap'])
            ->disableOriginalConstructor()
            ->getMock();

        $attributeGroupNameToCodeMap->expects($this->once())
            ->method('getGroupCodeMap')
            ->with($groupName)
            ->willReturn($groupCode);

        $fieldName = 'fieldname';
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->at(1))->method('getValue')->with('attribute_set_id')->willReturn(1);
        $recordToHandle->expects($this->at(2))->method('getValue')->with('attribute_group_name')
            ->willReturn($groupName);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $groupCode);

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

        $handler = new SetGroupCode($config, $source, $attributeGroupNameToCodeMap);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
