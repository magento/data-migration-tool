<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

/**
 * Class DataTest
 * @dbFixture tier_price
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    const TIRE_PRICE_TABLE_DESTINATION = 'catalog_product_entity_tier_price';
    
    /**
     * @var \Migration\ResourceModel\Destination
     */
    private $destination;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $progress;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    private $source;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $recordFactory;

    /**
     * @var \Migration\Step\TierPrice\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Migration\RecordTransformerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $recordTransformerFactory;

    /**
     * @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapFactory;

    /**
     * @return void
     */
    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->progress = $objectManager->create('Migration\App\ProgressBar\LogLevelProcessor');
        $this->source = $objectManager->create('Migration\ResourceModel\Source');
        $this->destination = $objectManager->create('Migration\ResourceModel\Destination');
        $this->recordFactory = $objectManager->create('Migration\ResourceModel\RecordFactory');
        $this->helper = $objectManager->create('Migration\Step\TierPrice\Helper');
        $this->logger = $objectManager->create('Migration\Logger\Logger');
        $this->recordTransformerFactory = $objectManager->create('Migration\RecordTransformerFactory');
        $this->mapFactory = $objectManager->create('Migration\Reader\MapFactory');
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $data = new Data(
            $this->progress,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->logger,
            $this->helper,
            $this->recordTransformerFactory,
            $this->mapFactory
        );
        $this->assertTrue($data->perform());
        $this->assertEquals(2, count($this->destination->getRecords(self::TIRE_PRICE_TABLE_DESTINATION, 0)));
    }
}
