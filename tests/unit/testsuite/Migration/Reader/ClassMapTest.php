<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Reader;

/**
 * Class ClassMap test
 */
class ClassMapTest extends \PHPUnit_Framework_TestCase
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
        $config = $this->getMockBuilder('\Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])
            ->getMock();
        $config->expects($this->once())->method('getOption')->with('class_map')
            ->willReturn('tests/unit/testsuite/Migration/_files/class-map.xml');

        $validationState = $this->getMockBuilder('Magento\Framework\App\Arguments\ValidationState')
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
            'Magento\\Catalog\\Block\\Product\\Widget\\NewWidget',
            $this->classMap->convertClassName('catalog/product_widget_new')
        );
    }

    /**
     * @return void
     */
    public function testConvertClassNameNotInMap()
    {
        $this->assertEquals(
            'catalog/product_widget_new_1',
            $this->classMap->convertClassName('catalog/product_widget_new_1')
        );
    }

    /**
     * @return void
     */
    public function testGetMap()
    {
        $this->assertEquals(
            $this->classMap->getMap('catalog/product_widget_new')['catalog/product_widget_new'],
            'Magento\\Catalog\\Block\\Product\\Widget\\NewWidget'
        );
    }
}
