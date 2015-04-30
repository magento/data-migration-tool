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

    protected $progress;
    protected $logger;
    protected $source;
    protected $recordTransformerFactory;
    protected $recordFactory;

    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->progress = $objectManager->create('Migration\App\ProgressBar\LogLevelProcessor');
        $this->logger = $objectManager->create('Migration\Logger\Logger');
        $this->source = $objectManager->create('Migration\Resource\Source');
        $this->destination = $objectManager->create('Migration\Resource\Destination');
        $this->recordTransformerFactory = $objectManager->create('Migration\RecordTransformerFactory');
        $this->recordFactory = $objectManager->create('Migration\Resource\RecordFactory');
    }

    public function testIntegrity()
    {
        $stores = new Stores(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->recordTransformerFactory,
            $this->recordFactory,
            'integrity'
        );
        $this->assertTrue($stores->perform());
    }

    public function testData()
    {
        $stores = new Stores(
            $this->progress,
            $this->logger,
            $this->source,
            $this->destination,
            $this->recordTransformerFactory,
            $this->recordFactory,
            'data'
        );
        $this->assertTrue($stores->perform());
        foreach ($this->destinationDocuments as $documentName => $recordsCount) {
            $this->assertEquals($recordsCount, count($this->destination->getRecords($documentName, 0)));
        }
    }
}
