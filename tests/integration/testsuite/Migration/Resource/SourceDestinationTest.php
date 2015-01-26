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
    /**
     * @var \Migration\Resource\Source $source
     */
    protected $source;

    /** @var \Migration\Resource\Destination $destination */
    protected $destination;

    protected function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $this->source = $helper->get('\Migration\Resource\Source');
        $this->destination = $helper->get('\Migration\Resource\Destination');
    }

    public function testMigrate()
    {
        $sourceCount = $this->source->getRecordsCount('catalog_product_entity');
        $records = $this->source->getRecords('catalog_product_entity');

        $this->destination->saveRecords('catalog_product_entity', $records);
        $destinationCount = $this->source->getRecordsCount('catalog_product_entity');

        $this->assertEquals($sourceCount, $destinationCount);
    }
}
