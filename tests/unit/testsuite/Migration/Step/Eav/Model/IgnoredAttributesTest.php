<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Model;

use Migration\Step\Eav\Helper;
use Migration\Step\Eav\InitialData;

/**
 * Class IgnoredAttributesTest
 */
class IgnoredAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IgnoredAttributes
     */
    private $ignoredAttributes;

    /**
     * @var Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var InitialData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $initialData;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->helper = $this->getMockBuilder(\Migration\Step\Eav\Helper::class)->disableOriginalConstructor()
            ->setMethods(['getAttributesGroupCodes'])
            ->getMock();
        $this->initialData = $this->getMockBuilder(\Migration\Step\Eav\InitialData::class)->disableOriginalConstructor()
            ->setMethods(['getAttributes'])
            ->getMock();
        $this->ignoredAttributes = new IgnoredAttributes($this->helper, $this->initialData);
    }

    /**
     * @return void
     */
    public function testClearIgnoredAttributes()
    {
        $allSourceRecords = [
            0 => [
                'attribute_code' => 'attribute_code_3_ignored',
                'entity_type_id' => 111,
                'attribute_id' => 54,
            ],
            1 => [
                'attribute_code' => 'attribute_code_1',
                'entity_type_id' => 1,
                'attribute_id' => 55,
            ],
            2 => [
                'attribute_code' => 'attribute_code_2',
                'entity_type_id' => 2,
                'attribute_id' => 56,
            ]
        ];
        $ignoredSourceRecords = ['attribute_code_3_ignored' => [111]];
        $clearedSourceRecords = [
            1 => [
                'attribute_code' => 'attribute_code_1',
                'entity_type_id' => 1,
                'attribute_id' => 55,
            ],
            2 => [
                'attribute_code' => 'attribute_code_2',
                'entity_type_id' => 2,
                'attribute_id' => 56,
            ]
        ];
        $this->initialData->expects($this->once())->method('getAttributes')->with('source')
            ->willReturn($allSourceRecords);
        $this->helper->expects($this->once())->method('getAttributesGroupCodes')->with('ignore')
            ->willReturn($ignoredSourceRecords);
        $this->assertEquals($clearedSourceRecords, $this->ignoredAttributes->clearIgnoredAttributes($allSourceRecords));
    }
}
