<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Resource source and destination test class
 */
class SourceDestinationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Source $source
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination $destination
     */
    protected $destination;

    protected function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')->init($helper->getConfigPath());
        $this->source = $objectManager->get('\Migration\Resource\Source');
        $this->destination = $objectManager->get('\Migration\Resource\Destination');
    }

    public function testMigrate()
    {
        $sourceCount = $this->source->getRecordsCount('catalog_product_entity');
        $document = $this->source->getDocument('catalog_product_entity');
        $records = $document->getRecordIterator();
        $records->setRecordProvider($this->source->getAdapter());
        $this->destination->saveRecords('catalog_product_entity', $records);
        //$this->destination->saveRecords('catalog_product_entity', $records);
        $destinationCount = $this->source->getRecordsCount('catalog_product_entity');

        $this->assertEquals($sourceCount, $destinationCount);
    }
}
