<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $entityType = 'catalog_product';
        $attributeSetId = '4';
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
            ->with($groupName, $entityType)
            ->willReturn($groupCode);
        $recordToHandle->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $recordToHandle->expects($this->any())->method('getValue')->willReturnMap(
            [
                ['attribute_group_name', $groupName],
                ['attribute_set_id', $attributeSetId]
            ]
        );
        $recordToHandle->expects($this->once())->method('setValue')->with($fieldName, $groupCode);
        $config = $this->getMockBuilder(\Migration\Config::class)
            ->disableOriginalConstructor()->setMethods(['getSource'])->getMock();
        $config->expects($this->once())->method('getSource')->willReturn(['type' => DatabaseStage::SOURCE_TYPE]);

        $source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)
            ->setMethods(['getAdapter', 'addDocumentPrefix'])
            ->disableOriginalConstructor()
            ->getMock();
        $mySqlAdapter = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Mysql::class,
            ['getSelect', 'fetchOne']
        );
        $dbSelect = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['from', 'where', 'join', 'getAdapter']
        );
        $mySqlAdapter->expects($this->any())->method('getSelect')->willReturn($dbSelect);
        $source->expects($this->any())->method('getAdapter')->willReturn($mySqlAdapter);
        $source->expects($this->any())->method('addDocumentPrefix')->willReturnArgument(0);
        $dbSelect->expects($this->any())->method('from')->willReturnSelf();
        $dbSelect->expects($this->any())->method('where')->willReturnSelf();
        $dbSelect->expects($this->any())->method('join')->willReturnSelf();
        $dbSelect->expects($this->any())->method('getAdapter')->willReturn($mySqlAdapter);
        $mySqlAdapter->expects($this->once())->method('fetchOne')->willReturn($entityType);

        $handler = new SetGroupCode($config, $attributeGroupNameToCodeMap, $source);
        $handler->setField($fieldName);
        $handler->handle($recordToHandle, $oppositeRecord);
    }
}
