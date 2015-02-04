<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Structure
     */
    protected $structure;

    protected function setUp()
    {
        $this->structure = new \Migration\Resource\Structure(['id' => 'int', 'name' => 'varchar']);
    }

    public function testGetFields()
    {
        $this->assertEquals(['id' => 'int', 'name' => 'varchar'], $this->structure->getFields());
    }

    public function testHasField()
    {
        $this->assertTrue($this->structure->hasField('name'));
    }

    public function testNotHasField()
    {
        $this->assertFalse($this->structure->hasField('new_name'));
    }
}
