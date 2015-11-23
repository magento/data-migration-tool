<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

class PlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testHandle()
    {
        $classMapData = ['catalog/product_widget_new' => 'Magento\Catalog\Block\Product\Widget\NewWidget'];
        $content = '<p>hello1 {{widget type="catalog/product_widget_new" display_type="all_products" '
            . 'products_count="10" template="catalog/product/widget/new/content/new_grid.phtml"}}</p>';
        $contentConverted = '<p>hello1 {{widget type="Magento\\\\Catalog\\\\Block\\\\Product\\\\Widget\\\\NewWidget"'
            .' display_type="all_products" products_count="10" template="product/widget/new/content/new_grid.phtml"}}'
            . '</p>';
        $fieldName = 'fieldname';
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMock(
            'Migration\ResourceModel\Record',
            ['getValue', 'setValue', 'getFields'],
            [],
            '',
            false
        );
        $record->expects($this->once())->method('getValue')->with($fieldName)->willReturn($content);
        $record->expects($this->once())->method('setValue')->with($fieldName, $contentConverted);
        $record->expects($this->once())->method('getFields')->will($this->returnValue([$fieldName]));
        $classMap = $this->getMock('Migration\Reader\ClassMap', ['getMap'], [], '', false);
        $classMap->expects($this->once())->method('getMap')->willReturn($classMapData);
        $record2 = $this->getMockBuilder('Migration\ResourceModel\Record')->disableOriginalConstructor()->getMock();
        $handler = new \Migration\Handler\Placeholder($classMap);
        $handler->setField($fieldName);
        $handler->handle($record, $record2);
    }
}
