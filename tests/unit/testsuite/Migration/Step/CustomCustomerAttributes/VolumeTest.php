<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\CustomCustomerAttributes;

use Migration\Step\CustomCustomerAttributesTest;

/**
 * Class VolumeTest
 */
class VolumeTest extends CustomCustomerAttributesTest
{
    public function testVolumeCheck()
    {
        $this->step = new Volume(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress
        );
        $fields = ['field_name' => []];

        $structure = $this->getMockBuilder('Migration\Resource\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));

        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()
            ->setMethods(['getStructure'])
            ->getMock();
        $document->expects($this->exactly(8))->method('getStructure')->will($this->returnValue($structure));

        $this->source->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));

        $this->source->expects($this->exactly(4))->method('getRecordsCount')->with()->will($this->returnValue(1));
        $this->destination->expects($this->exactly(4))->method('getRecordsCount')->with()->will($this->returnValue(1));

        $this->assertTrue($this->step->perform());
    }
}
