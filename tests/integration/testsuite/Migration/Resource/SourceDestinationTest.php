<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Resource source test class
 */
class SourceDestinationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        /** @var \Migration\Resource\Source $source */
        $this->source = \Migration\TestFramework\Helper::getInstance()->getObjectManager()->get('\Migration\Resource\Source');
        /** @var \Migration\Resource\Destination $destination */
        $this->destination = \Migration\TestFramework\Helper::getInstance()->getObjectManager()->get('\Migration\Resource\Destination');
    }

    public function testGetNextBunch()
    {
        $this->source->setResourceUnitName('catalog_product_entity');
        $data = $this->source->getNextBunch();
        $this->destination->setResourceUnitName('catalog_product_entity');
        $this->destination->save($data);
    }
}
