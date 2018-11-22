<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

/**
 * Class DataTest
 * @dbFixture customer
 */
class DataTest extends \PHPUnit\Framework\TestCase
{
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
     * @var \Migration\Step\Customer\Model\AttributesToStatic|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributesToStatic;

    /**
     * @var \Migration\Step\Customer\Model\AttributesDataToSkip|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributesDataToSkip;

    /**
     * @var \Migration\Step\Customer\Model\AttributesDataToCustomerEntityRecords|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributesDataToCustomerEntityRecords;

    /**
     * @var \Migration\Reader\GroupsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupsFactory;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    /**
     * @return void
     */
    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $this->config = $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->progress = $objectManager->create(\Migration\App\ProgressBar\LogLevelProcessor::class);
        $this->source = $objectManager->create(\Migration\ResourceModel\Source::class);
        $this->destination = $objectManager->create(\Migration\ResourceModel\Destination::class);
        $this->recordFactory = $objectManager->create(\Migration\ResourceModel\RecordFactory::class);
        $this->helper = $objectManager->create(\Migration\Step\TierPrice\Helper::class);
        $this->logger = $objectManager->create(\Migration\Logger\Logger::class);
        $this->recordTransformerFactory = $objectManager->create(\Migration\RecordTransformerFactory::class);
        $this->mapFactory = $objectManager->create(\Migration\Reader\MapFactory::class);
        $this->attributesToStatic = $objectManager->create(\Migration\Step\Customer\Model\AttributesToStatic::class);
        $this->attributesDataToSkip = $objectManager->create(
            \Migration\Step\Customer\Model\AttributesDataToSkip::class
        );
        $this->attributesDataToCustomerEntityRecords = $objectManager->create(
            \Migration\Step\Customer\Model\AttributesDataToCustomerEntityRecords::class
        );
        $this->groupsFactory = $objectManager->create(\Migration\Reader\GroupsFactory::class);
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $data = new Data(
            $this->config,
            $this->progress,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->recordTransformerFactory,
            $this->attributesDataToCustomerEntityRecords,
            $this->attributesDataToSkip,
            $this->attributesToStatic,
            $this->mapFactory,
            $this->groupsFactory,
            $this->logger
        );
        $this->assertTrue($data->perform());
        $this->assertEquals(2, count($this->destination->getRecords('customer_entity', 0)));
        $this->assertEquals(6, count($this->destination->getRecords('customer_entity_int', 0)));
        $this->assertEquals(0, count($this->destination->getRecords('customer_entity_varchar', 0)));
        $this->assertEquals(4, count($this->destination->getRecords('customer_address_entity', 0)));
        $this->assertEquals(3, count($this->destination->getRecords('customer_address_entity_varchar', 0)));
        $this->assertEquals(0, count($this->destination->getRecords('customer_address_entity_int', 0)));
        $this->assertEquals(0, count($this->destination->getRecords('customer_address_entity_text', 0)));
    }
}
