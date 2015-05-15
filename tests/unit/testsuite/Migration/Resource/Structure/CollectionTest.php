<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Structure;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var []
     */
    protected $structures;

    /**
     * @var \Migration\Resource\Structure\Collection
     */
    protected $structureCollection;

    protected function setUp()
    {
        $structure1 = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structure2 = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $structure3 = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $this->structures = ['table1' => $structure1, 'table2' => $structure2, 'table3' => $structure3];
        $this->structureCollection = new \Migration\Resource\Structure\Collection($this->structures);
    }

    public function testAddStructure()
    {
        $this->assertEquals(3, count($this->structureCollection));
        $structure = $this->getMock('\Migration\Resource\Structure', [], [], '', false);
        $this->structureCollection->addStructure('table4', $structure);
        $this->assertEquals(4, count($this->structureCollection));
    }

    public function testGetStructure()
    {
        $this->assertEquals($this->structures['table2'], $this->structureCollection->getStructure('table2'));
    }

    public function testGetStructureNotExists()
    {
        $this->assertNull($this->structureCollection->getStructure('table5'));
    }
}
