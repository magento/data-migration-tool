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
     * @param string $entityType
     * @param string $groupCode
     * @return void
     */
    public function testGetGroupCodeMap($groupName, $entityType, $groupCode)
    {
        $result = $this->model->getGroupCodeMap($groupName, $entityType);
        $this->assertEquals($result, $groupCode);
    }

    /**
     * @return array
     */
    public function getGroupsData()
    {
        return [
            ['Migration_General', 'catalog_product', 'product-details'],
            ['Migration_Prices', 'catalog_product', 'advanced-pricing'],
            ['Migration_Design', 'catalog_product', 'design'],
            ['Migration_Something', 'catalog_product', 'migration-something'],
            ['Migration_General', 'catalog_category', 'migration-general'],
        ];
    }
}
