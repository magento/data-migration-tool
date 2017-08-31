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
class SetGroupCodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $groupName = 'Migration General';
        $groupCode = 'migration-general';
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $recordToHandle */
        $recordToHandle = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['getValue', 'setValue', 'getFields'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var Record $oppositeRecord|\PHPUnit_Framework_MockObject_MockObject */
        $oppositeRecord = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var AttributeGroupNameToCodeMap $attributeGroupNameToCodeMap|\PHPUnit_Framework_MockObject_MockObject */
        $attributeGroupNameToCodeMap = $this->getMockBuilder(\Migration\Model\Eav\AttributeGroupNameToCodeMap::class)
            ->setMethods(['getGroupCodeMap'])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeGroupNameToCodeMap->expects($this->once())
            ->method('getGroupCodeMap')
            ->with($groupName)
            ->willReturn($groupCode);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->once())->method('getValue')->with('attribute_group_name')
            ->willReturn($groupName);
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $groupCode);
        $config = $this->getMockBuilder(\Migration\Config::class)
            ->disableOriginalConstructor()->setMethods(['getSource'])->getMock();
        $config->expects($this->once())->method('getSource')->willReturn(['type' => DatabaseStage::SOURCE_TYPE]);
        $handler = new SetGroupCode($config, $attributeGroupNameToCodeMap);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
