<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

class StructureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Structure
     */
    protected $structure;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->structure = new \Migration\ResourceModel\Structure(['id' => 'int', 'name' => 'varchar']);
    }

    /**
     * @return void
     */
    public function testGetFields()
    {
        $this->assertEquals(['id' => 'int', 'name' => 'varchar'], $this->structure->getFields());
    }

    /**
     * @return void
     */
    public function testHasField()
    {
        $this->assertTrue($this->structure->hasField('name'));
    }

    /**
     * @return void
     */
    public function testNotHasField()
    {
        $this->assertFalse($this->structure->hasField('new_name'));
    }
}
