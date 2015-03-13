<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

/**
 * Class MapReaderTest
 */
class MapReaderLogTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $config = $this->getMockBuilder('Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getOption'])->getMock();
        $config->expects($this->once())->method('getOption')->with('log_map_file')->will(
            $this->returnValue('tests/unit/testsuite/Migration/_files/map.xml')
        );
        $this->assertInstanceOf('Migration\MapReaderInterface', new MapReaderLog($config));
    }
}
