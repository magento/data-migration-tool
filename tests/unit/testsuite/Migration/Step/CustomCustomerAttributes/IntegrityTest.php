<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\CustomCustomerAttributes;

use Migration\Step\CustomCustomerAttributesTest;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends CustomCustomerAttributesTest
{
    public function testPerform()
    {
        $this->step = new Integrity(
            $this->config,
            $this->source,
            $this->destination,
            $this->progress
        );
        $document = $this->getMockBuilder('Migration\Resource\Document')->disableOriginalConstructor()->getMock();

        $this->source->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));
        $this->destination->expects($this->exactly(4))->method('getDocument')->will($this->returnValue($document));

        $this->assertTrue($this->step->perform());
    }
}
