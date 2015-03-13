<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

/**
 * Class MapReaderTest
 */
class MapReaderEavTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MapReaderEav
     */
    protected $map;

    public function testCreate()
    {
        $config = $this->getMockBuilder('Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])->getMock();
        $config->expects($this->once())->method('getOption')->with('eav_map_file')->will(
            $this->returnValue('tests/unit/testsuite/Migration/_files/map.xml')
        );
        $this->assertInstanceOf('Migration\MapReaderInterface', new MapReaderEav($config));
    }
}
