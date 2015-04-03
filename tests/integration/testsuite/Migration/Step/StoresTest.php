<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class StoresTest
 * @dbFixture stores
 */
class StoresTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Stores
     */
    protected $stores;

    /**
     * @var \Migration\Resource\Destination
     */
    protected $destination;

    /**
     * @var array
     */
    protected $destinationDocuments = [
        'store' => 2,
        'store_group' => 2,
        'store_website' => 2
    ];

    public function setUp()
    {
        $objectManager = \Migration\TestFramework\Helper::getInstance()->getObjectManager();
        $objectManager->get('\Migration\Config')->init(dirname(__DIR__) . '/_files/config.xml');
        $progress = $objectManager->create('Migration\ProgressBar');
        $logger = $objectManager->create('Migration\Logger\Logger');
        $source = $objectManager->create('Migration\Resource\Source');
        $this->destination = $objectManager->create('Migration\Resource\Destination');
        $recordTransformerFactory = $objectManager->create('Migration\RecordTransformerFactory');
        $recordFactory = $objectManager->create('Migration\Resource\RecordFactory');
        $this->stores = new Stores(
            $progress,
            $logger,
            $source,
            $this->destination,
            $recordTransformerFactory,
            $recordFactory
        );
    }

    public function testIntegrity()
    {
        $this->assertTrue($this->stores->integrity());
    }

    public function testRun()
    {
        $this->assertTrue($this->stores->run());
        foreach ($this->destinationDocuments as $documentName => $recordsCount) {
            $this->assertEquals($recordsCount, count($this->destination->getRecords($documentName, 0)));
        }
    }

    public function volumeCheck()
    {
        $this->assertTrue($this->stores->volumeCheck());
    }
}
