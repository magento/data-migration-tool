<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger\Writer\Console;

class CreatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Creator */
    protected $creator;

    public function setUp()
    {
        $this->creator = new Creator();
    }

    public function testCreate()
    {
        $result = $this->creator->create();
        $this->assertInstanceOf('Zend\Console\Adapter\AdapterInterface', $result);
    }
}
