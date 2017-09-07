<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Model\Eav;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AttributeGroupNameToCodeMapTest
 */
class AttributeGroupNameToCodeMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\Model\Eav\AttributeGroupNameToCodeMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(\Migration\Model\Eav\AttributeGroupNameToCodeMap::class);
    }

    /**
     * @dataProvider getGroupsData()
     * @param string $groupName
     * @param string $groupCode
     * @return void
     */
    public function testGetGroupCodeMap($groupName, $groupCode)
    {
        $result = $this->model->getGroupCodeMap($groupName);
        $this->assertEquals($result, $groupCode);
    }

    /**
     * @return array
     */
    public function getGroupsData()
    {
        return [
            ['Migration_General', 'product-details'],
            ['Migration_Prices', 'advanced-pricing'],
            ['Migration_Design', 'design'],
            ['Migration_Something', 'migration-something'],
        ];
    }
}
