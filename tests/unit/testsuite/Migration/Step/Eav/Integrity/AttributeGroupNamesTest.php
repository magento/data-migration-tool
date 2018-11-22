<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Integrity;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Migration\Reader\MapInterface;
use Migration\Step\Eav\Integrity\AttributeGroupNames;
use Migration\Step\Eav\Helper;

/**
 * Class AttributeGroupNamesTest
 */
class AttributeGroupNamesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeGroupNames|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(\Migration\Step\Eav\Helper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSourceRecords'])
            ->getMock();

        $objectHelper = new ObjectManager($this);
        $groupNameToCodeMap = $objectHelper->getObject(\Migration\Model\Eav\AttributeGroupNameToCodeMap::class);

        $this->model = $objectHelper->getObject(\Migration\Step\Eav\Integrity\AttributeGroupNames::class, [
            'helper' => $this->helper,
            'groupNameToCodeMap' => $groupNameToCodeMap
        ]);
    }

    /**
     * @dataProvider getFixtureData()
     * @param array $sourceData
     * @param int $errorsCount
     * @return void
     */
    public function testCheckAttributeGroupNames($sourceData, $errorsCount)
    {
        $this->helper->expects($this->any())->method('getSourceRecords')->willReturnMap([
            ['eav_attribute_group', ['attribute_group_id'], $sourceData['eav_attribute_group']],
            ['eav_entity_type', ['entity_type_code'], $sourceData['eav_entity_type']],
            ['eav_attribute_set', ['attribute_set_id'], $sourceData['eav_attribute_set']]
        ]);

        $result = $this->model->checkAttributeGroupNames();
        if (array_key_exists(MapInterface::TYPE_SOURCE, $result)) {
            $result = $result[MapInterface::TYPE_SOURCE];
        }

        $this->assertCount($errorsCount, $result);
    }

    /**
     * @return array
     */
    public function getFixtureData()
    {
        return [
            [
                [
                    'eav_attribute_group' => [
                        7 => ['attribute_set_id' => 4, 'attribute_group_name' => 'General'],
                        8 => ['attribute_set_id' => 4, 'attribute_group_name' => 'Prices'],
                        11 => ['attribute_set_id' => 4, 'attribute_group_name' => 'Design'],
                        14 => ['attribute_set_id' => 4, 'attribute_group_name' => 'Images']
                    ],
                    'eav_entity_type' => [
                        'catalog_product' => ['entity_type_id' => 4]
                    ],
                    'eav_attribute_set' => [
                        3 => ['attribute_set_id' => 3, 'entity_type_id' => 3, 'attribute_set_name' => 'Default'],
                        4 => ['attribute_set_id' => 4, 'entity_type_id' => 4, 'attribute_set_name' => 'Default']
                    ]
                ],
                0
            ], [
                [
                    'eav_attribute_group' => [
                        5 => ['attribute_set_id' => 2, 'attribute_group_name' => 'Prices'],
                        7 => ['attribute_set_id' => 3, 'attribute_group_name' => 'General'],
                        8 => ['attribute_set_id' => 3, 'attribute_group_name' => 'Prices'],
                        11 => ['attribute_set_id' => 3, 'attribute_group_name' => 'Design'],
                        12 => ['attribute_set_id' => 4, 'attribute_group_name' => 'General'],
                        14 => ['attribute_set_id' => 4, 'attribute_group_name' => 'Images']
                    ],
                    'eav_entity_type' => [
                        'catalog_product' => ['entity_type_id' => 4]
                    ],
                    'eav_attribute_set' => [
                        1 => ['attribute_set_id' => 1, 'entity_type_id' => 3, 'attribute_set_name' => 'Default'],
                        2 => ['attribute_set_id' => 2, 'entity_type_id' => 4, 'attribute_set_name' => 'Default'],
                        3 => ['attribute_set_id' => 3, 'entity_type_id' => 4, 'attribute_set_name' => 'Default_2'],
                        4 => ['attribute_set_id' => 4, 'entity_type_id' => 4, 'attribute_set_name' => 'Default_3']
                    ]
                ],
                3
            ], [
                [
                    'eav_attribute_group' => [
                        2 => ['attribute_set_id' => 2, 'attribute_group_name' => 'General'],
                        7 => ['attribute_set_id' => 3, 'attribute_group_name' => 'General'],
                        8 => ['attribute_set_id' => 3, 'attribute_group_name' => 'Prices'],
                        11 => ['attribute_set_id' => 3, 'attribute_group_name' => 'Design'],
                        12 => ['attribute_set_id' => 4, 'attribute_group_name' => 'General'],
                        14 => ['attribute_set_id' => 4, 'attribute_group_name' => 'Images']
                    ],
                    'eav_entity_type' => [
                        'customer' => ['entity_type_id' => 1],
                        'catalog_product' => ['entity_type_id' => 4],
                        'order' => ['entity_type_id' => 5]
                    ],
                    'eav_attribute_set' => [
                        1 => ['attribute_set_id' => 1, 'entity_type_id' => 2, 'attribute_set_name' => 'Custom'],
                        2 => ['attribute_set_id' => 2, 'entity_type_id' => 3, 'attribute_set_name' => 'Default'],
                        3 => ['attribute_set_id' => 3, 'entity_type_id' => 4, 'attribute_set_name' => 'Default_2'],
                        4 => ['attribute_set_id' => 4, 'entity_type_id' => 4, 'attribute_set_name' => 'Default_3']
                    ]
                ],
                2
            ],
        ];
    }
}
