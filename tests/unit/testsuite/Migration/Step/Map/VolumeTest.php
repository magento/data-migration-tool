<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Volume
     */
    protected $volume;

    public function setUp()
    {
        $this->volume = new Volume();
    }

    public function testPerform()
    {
        $this->assertTrue($this->volume->perform());
    }
}
