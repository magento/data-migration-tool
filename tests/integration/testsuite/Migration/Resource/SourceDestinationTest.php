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
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->source = $objectManager->get('\Migration\Resource\Source');
        $this->destination = $objectManager->get('\Migration\Resource\Destination');
    }

    public function testGetRecordsCount()
    {
        $sourceCount = $this->source->getRecordsCount('table_with_data');
        $destinationCount = $this->destination->getRecordsCount('table_without_data');
        $this->assertEquals(7, $sourceCount);
        $this->assertEquals(0, $destinationCount);
    }

    public function testGetFields()
    {
        $sourceStruct = $this->source->getDocument('table_with_data')->getStructure()->getFields();
        $destStruct = $this->destination->getDocument('table_without_data')->getStructure()->getFields();
        $this->assertEquals(array_keys($sourceStruct), array_keys($destStruct));
    }
}
