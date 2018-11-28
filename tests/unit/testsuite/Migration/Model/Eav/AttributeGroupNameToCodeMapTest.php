<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    private $model;

    /**
     * @var \Magento\Framework\Filter\Translit|\PHPUnit_Framework_MockObject_MockObject
     */
    private $translitFilter;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->translitFilter = $this->getMockBuilder(\Magento\Framework\Filter\Translit::class)
            ->disableOriginalConstructor()
            ->setMethods(['filter'])
            ->getMock();
        $objectHelper = new ObjectManager($this);
        $this->model = $objectHelper->getObject(
            \Migration\Model\Eav\AttributeGroupNameToCodeMap::class,
            ['translitFilter' => $this->translitFilter]
        );
    }

    /**
     * @dataProvider getGroupsData()
     * @param string $groupName
     * @param string $entityType
     * @param string $groupCode
     * @param string $groupCodeTranslit
     * @return void
     */
    public function testGetGroupCodeMap($groupName, $entityType, $groupCode, $groupCodeTranslit)
    {
        $this->translitFilter->expects($this->once())->method('filter')->with(strtolower($groupName))
            ->willReturn($groupCodeTranslit);
        $result = $this->model->getGroupCodeMap($groupName, $entityType);
        $this->assertEquals($result, $groupCode);
    }

    /**
     * @return array
     */
    public function getGroupsData()
    {
        return [
            ['Migration_General', 'catalog_product', 'product-details', 'migration-general'],
            ['Migration_Prices', 'catalog_product', 'advanced-pricing', 'migration-prices'],
            ['Migration_Design', 'catalog_product', 'design', 'migration-design'],
            ['Migration_Something', 'catalog_product', 'migration-something', 'migration-something'],
            ['Migration_General', 'catalog_category', 'migration-general', 'migration-general'],
            ['Migration_Кирилица', 'customer_address', 'migration-kirilica', 'migration-kirilica'],
        ];
    }
}
