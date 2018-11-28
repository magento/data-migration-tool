<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Integrity;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Migration\Step\Eav\Helper;
use Migration\Step\Eav\Model\IgnoredAttributes;

/**
 * Class AttributeFrontendInputTest
 */
class AttributeFrontendInputTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeFrontendInput|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var IgnoredAttributes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ignoredAttributes;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->helper = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSourceRecords', 'getAttributesGroupCodes'])
            ->getMock();
        $this->ignoredAttributes = $this->getMockBuilder(IgnoredAttributes::class)
            ->disableOriginalConstructor()
            ->setMethods(['clearIgnoredAttributes'])
            ->getMock();

        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(AttributeFrontendInput::class, [
            'helper' => $this->helper,
            'ignoredAttributes' => $this->ignoredAttributes
        ]);
    }

    /**
     * @dataProvider getFixtureData()
     * @param array $groupCodes
     * @param int $errorsCount
     * @return void
     */
    public function testCheckAttributeGroupNames($groupCodes, $errorsCount)
    {
        $sourceRecords = [
            [
                'attribute_id' => '60',
                'entity_type_id' => '4',
                'attribute_code' => 'name',
                'frontend_input' => 'text'
            ], [
                'attribute_id' => '61',
                'entity_type_id' => '4',
                'attribute_code' => 'description',
                'frontend_input' => 'textarea'
            ], [
                'attribute_id' => '63',
                'entity_type_id' => '4',
                'attribute_code' => 'sku',
                'frontend_input' => 'text'
            ], [
                'attribute_id' => '169',
                'entity_type_id' => '4',
                'attribute_code' => 'group_price',
                'frontend_input' => null
            ],
            [
                'attribute_id' => '70',
                'entity_type_id' => '4',
                'attribute_code' => 'manufacturer',
                'frontend_input' => 'select'
            ], [
                'attribute_id' => '84',
                'entity_type_id' => '4',
                'attribute_code' => 'status',
                'frontend_input' => 'select'
            ],
            [
                'attribute_id' => '143',
                'entity_type_id' => '4',
                'attribute_code' => 'msrp_enabled',
                'frontend_input' => ''
            ],
        ];
        $this->helper
            ->expects($this->any())
            ->method('getSourceRecords')
            ->willReturn($sourceRecords);
        $this->ignoredAttributes
            ->expects($this->any())
            ->method('clearIgnoredAttributes')
            ->with($sourceRecords)
            ->willReturn($sourceRecords);

        $this->helper->expects($this->any())->method('getAttributesGroupCodes')->willReturnMap($groupCodes);
        $this->assertCount($errorsCount, $this->model->checkAttributeFrontendInput());
    }

    /**
     * @return array
     */
    public function getFixtureData()
    {
        return [
            [
                [
                    ['frontend_input_empty_allowed', ['group_price' => ['4']]]
                ],
                1
            ], [
                [
                    ['frontend_input_empty_allowed', ['msrp_enabled' => ['4']]]
                ],
                1
            ],[
                [
                    ['frontend_input_empty_allowed', []]
                ],
                2
            ],[
                [
                    ['frontend_input_empty_allowed', ['group_price' => ['10']]]
                ],
                2
            ],
        ];
    }
}
