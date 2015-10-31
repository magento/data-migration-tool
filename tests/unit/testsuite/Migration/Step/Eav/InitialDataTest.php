<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Class InitialDataTest
 */
class InitialDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\Eav\InitialData
     */
    protected $initialData;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\Reader\Map|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $map;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->map = $this->getMockBuilder('\Migration\Reader\Map')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->getMock('\Migration\Reader\MapFactory', [], [], '', false);
        $mapFactory->expects($this->any())->method('create')->with('eav_map_file')->willReturn($this->map);

        $this->source = $this->getMockBuilder('\Migration\ResourceModel\Source')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->destination = $this->getMockBuilder('\Migration\ResourceModel\Destination')->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->helper = $this->getMockBuilder('\Migration\Step\Eav\Helper')->disableOriginalConstructor()
            ->setMethods(['getSourceRecords', 'getDestinationRecords'])
            ->getMock();
        $this->initialData = new InitialData($mapFactory, $this->source, $this->destination, $this->helper);
    }

    /**
     * @covers \Migration\Step\Eav\InitialData::Init
     * @covers \Migration\Step\Eav\InitialData::initAttributes
     * @covers \Migration\Step\Eav\InitialData::initAttributeSets
     * @covers \Migration\Step\Eav\InitialData::initAttributeGroups
     * @covers \Migration\Step\Eav\InitialData::getAttributes
     * @covers \Migration\Step\Eav\InitialData::getAttributeSets
     * @covers \Migration\Step\Eav\InitialData::getAttributeGroups
     * @return void
     */
    public function testInit()
    {
        $dataAttributes = [
            'source' => ['id_1' => 'value_1','id_2' => 'value_2'],
            'dest' => ['id_1' => 'value_1','id_2' => 'value_2']
        ];
        $attributeSets = ['attr_set_1', 'attr_set_2'];
        $attributeGroups = ['attr_group_1', 'attr_group_2'];
        $this->helper->expects($this->once())->method('getSourceRecords')->willReturnMap(
            [['eav_attribute', ['attribute_id'], $dataAttributes['source']]]
        );
        $this->helper->expects($this->any())->method('getDestinationRecords')->willReturnMap(
            [
                ['eav_attribute', ['entity_type_id', 'attribute_code'], $dataAttributes['dest']],
                ['eav_attribute_set', ['attribute_set_id'], $attributeSets],
                ['eav_attribute_group', ['attribute_set_id', 'attribute_group_name'], $attributeGroups]
            ]
        );
        $this->initialData->init();
        foreach ($dataAttributes as $resourceType => $resourceData) {
            $this->assertEquals($resourceData, $this->initialData->getAttributes($resourceType));
        }
        $this->assertEquals($attributeSets, $this->initialData->getAttributeSets('dest'));
        $this->assertEquals($attributeGroups, $this->initialData->getAttributeGroups('dest'));
    }
}
