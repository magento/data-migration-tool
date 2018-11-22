<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

/**
 * Class InitialDataTest
 */
class InitialDataTest extends \PHPUnit\Framework\TestCase
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
        $this->map = $this->getMockBuilder(\Migration\Reader\Map::class)->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        /** @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject $mapFactory */
        $mapFactory = $this->createMock(\Migration\Reader\MapFactory::class);
        $mapFactory->expects($this->any())->method('create')->with('eav_map_file')->willReturn($this->map);

        $this->source = $this->getMockBuilder(\Migration\ResourceModel\Source::class)->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->destination = $this->getMockBuilder(\Migration\ResourceModel\Destination::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->helper = $this->getMockBuilder(\Migration\Step\Eav\Helper::class)->disableOriginalConstructor()
            ->setMethods(['getSourceRecords', 'getDestinationRecords'])
            ->getMock();

        $dataAttributes = $this->getDataAttributes();
        $eavEntityTypes = [
            'source' => [
                ['entity_type_id' => '1', 'entity_type_code' => 'customer'],
                ['entity_type_id' => '2', 'entity_type_code' => 'customer_address'],
                ['entity_type_id' => '3', 'entity_type_code' => 'catalog_category'],
                ['entity_type_id' => '4', 'entity_type_code' => 'catalog_product'],
                ['entity_type_id' => '5', 'entity_type_code' => 'order'],
                ['entity_type_id' => '6', 'entity_type_code' => 'invoice'],
                ['entity_type_id' => '7', 'entity_type_code' => 'creditmemo'],
                ['entity_type_id' => '8', 'entity_type_code' => 'shipment']
            ],
            'dest' => [
                ['entity_type_id' => '1', 'entity_type_code' => 'customer'],
                ['entity_type_id' => '2', 'entity_type_code' => 'customer_address'],
                ['entity_type_id' => '3', 'entity_type_code' => 'catalog_category'],
                ['entity_type_id' => '4', 'entity_type_code' => 'catalog_product'],
                ['entity_type_id' => '5', 'entity_type_code' => 'some_m2_type']
            ],
        ];
        $attributeSets = ['attr_set_1', 'attr_set_2'];
        $attributeGroups = ['attr_group_1', 'attr_group_2'];
        $this->helper->expects($this->any())->method('getSourceRecords')->willReturnMap([
            ['eav_attribute', ['attribute_id'], $dataAttributes['source']],
            ['eav_entity_type', [], $eavEntityTypes['source']]
        ]);
        $this->helper->expects($this->any())->method('getDestinationRecords')->willReturnMap(
            [
                ['eav_attribute', ['entity_type_id', 'attribute_code'], $dataAttributes['dest']],
                ['eav_attribute_set', ['attribute_set_id'], $attributeSets],
                ['eav_attribute_group', ['attribute_set_id', 'attribute_group_name'], $attributeGroups],
                ['eav_entity_type', [], $eavEntityTypes['dest']]
            ]
        );

        $this->initialData = new InitialData($mapFactory, $this->source, $this->destination, $this->helper);
    }

    /**
     * @return void
     */
    public function testAttributes()
    {
        $dataAttributes = $this->getDataAttributes();
        foreach ($dataAttributes as $resourceType => $resourceData) {
            $this->assertEquals($resourceData, $this->initialData->getAttributes($resourceType));
        }
    }

    /**
     * @return void
     */
    public function testAttributeSets()
    {
        $attributeSets = ['attr_set_1', 'attr_set_2'];
        $this->assertEquals($attributeSets, $this->initialData->getAttributeSets('dest'));
    }

    /**
     * @return void
     */
    public function testAttributeGroups()
    {
        $attributeGroups = ['attr_group_1', 'attr_group_2'];
        $this->assertEquals($attributeGroups, $this->initialData->getAttributeGroups('dest'));
    }

    /**
     * @return array
     */
    private function getDataAttributes()
    {
        return [
            'source' => ['id_1' => 'value_1','id_2' => 'value_2'],
            'dest' => ['id_1' => 'value_1','id_2' => 'value_2']
        ];
    }
}
