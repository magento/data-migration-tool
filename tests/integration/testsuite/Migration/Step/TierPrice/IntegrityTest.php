<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

/**
 * Class IntegrityTest
 * @dbFixture tier_price
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
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
     * @var \Migration\Step\TierPrice\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $helper;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Migration\Reader\MapFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapFactory;

    /**
     * @var \Migration\Config
     */
    private $config;

    /**
     * @return void
     */
    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->progress = $objectManager->create(\Migration\App\ProgressBar\LogLevelProcessor::class);
        $this->source = $objectManager->create(\Migration\ResourceModel\Source::class);
        $this->destination = $objectManager->create(\Migration\ResourceModel\Destination::class);
        $this->helper = $objectManager->create(\Migration\Step\TierPrice\Helper::class);
        $this->logger = $objectManager->create(\Migration\Logger\Logger::class);
        $this->mapFactory = $objectManager->create(\Migration\Reader\MapFactory::class);
        $this->config = $objectManager->create(\Migration\Config::class);
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $data = new Integrity(
            $this->helper,
            $this->logger,
            $this->config,
            $this->progress,
            $this->source,
            $this->destination,
            $this->mapFactory
        );
        $this->assertTrue($data->perform());
    }

    /**
     * @return void
     */
    public function testPerformFail()
    {
        $data = new Integrity(
            $this->helper,
            $this->logger,
            $this->config,
            $this->progress,
            $this->source,
            $this->destination,
            $this->mapFactory,
            'tier_price_map_file_fail'
        );
        $this->assertFalse($data->perform());
        $logOutput = \Migration\Logger\Logger::getMessages();
        $error = 'Source fields are not mapped. Document: catalog_product_entity_tier_price. Fields: custom_field';
        $this->assertEquals($error, $logOutput[\Monolog\Logger::ERROR][0]);
    }
}
