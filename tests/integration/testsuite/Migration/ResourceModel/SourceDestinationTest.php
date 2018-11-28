<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

/**
 * ResourceModel source and destination test class
 */
class SourceDestinationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Source $source
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination $destination
     */
    protected $destination;

    /**
     * @return void
     */
    protected function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->source = $objectManager->get(\Migration\ResourceModel\Source::class);
        $this->destination = $objectManager->get(\Migration\ResourceModel\Destination::class);
    }

    /**
     * @return void
     */
    public function testGetRecordsCount()
    {
        $sourceCount = $this->source->getRecordsCount('table_with_data');
        $destinationCount = $this->destination->getRecordsCount('table_without_data');
        $this->assertEquals(7, $sourceCount);
        $this->assertEquals(0, $destinationCount);
    }

    /**
     * @return void
     */
    public function testGetFields()
    {
        $sourceStruct = $this->source->getDocument('table_with_data')->getStructure()->getFields();
        $destStruct = $this->destination->getDocument('table_without_data')->getStructure()->getFields();
        $this->assertEquals(array_keys($sourceStruct), array_keys($destStruct));
    }
}
