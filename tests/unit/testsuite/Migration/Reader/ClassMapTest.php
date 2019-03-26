<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class ClassMap test
 */
class ClassMapTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ClassMap
     */
    protected $classMap;

    /**
     * @return void
     */
    public function setUp()
    {
        $config = $this->getMockBuilder(\Migration\Config::class)->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $config->expects($this->once())->method('getOption')->with('class_map')
            ->willReturn('tests/unit/testsuite/Migration/_files/class-map.xml');

        $validationState = $this->getMockBuilder(\Magento\Framework\App\Arguments\ValidationState::class)
            ->disableOriginalConstructor()
            ->setMethods(['isValidationRequired'])
            ->getMock();

        $validationState->expects($this->any())->method('isValidationRequired')->willReturn(true);

        $this->classMap = new ClassMap($config, $validationState);
    }

    /**
     * @return void
     */
    public function testConvertClassName()
    {
        $this->assertEquals(
            \Magento\Catalog\Block\Product\Widget\Link::class,
            $this->classMap->convertClassName('catalog/product_widget_link')
        );
    }

    /**
     * @return void
     */
    public function testConvertClassNameNotInMap()
    {
        $this->assertFalse($this->classMap->hasMap('catalog/product_widget_link_1'));
    }

    /**
     * @return void
     */
    public function testGetMap()
    {
        $this->assertEquals(
            $this->classMap->getMap('catalog/product_widget_new')['catalog/product_widget_link'],
            \Magento\Catalog\Block\Product\Widget\Link::class
        );
    }
}
