<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

/**
 * Class IntegrityTest
 * @dbFixture stores
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Destination
     */
    private $destination;

    /**
     * @var array
     */
    private $destinationDocuments = [
        'store' => 2,
        'store_group' => 2,
        'store_website' => 2
    ];

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor
     */
    private $progress;

    /**
     * @var \Migration\ResourceModel\Source
     */
    private $source;

    /**
     * @var \Migration\Step\Stores\Model\DocumentsList
     */
    private $documentsList;

    /**
     * @var \Migration\Logger\Logger
     */
    private $logger;

    /**
     * @var \Migration\Reader\MapFactory
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
        $this->logger = $objectManager->create(\Migration\Logger\Logger::class);
        $this->source = $objectManager->create(\Migration\ResourceModel\Source::class);
        $this->destination = $objectManager->create(\Migration\ResourceModel\Destination::class);
        $this->documentsList = $objectManager->create(\Migration\Step\Stores\Model\DocumentsList::class);
        $this->mapFactory = $objectManager->create(\Migration\Reader\MapFactory::class);
        $this->config = $objectManager->create(\Migration\Config::class);
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $integrity = new Integrity(
            $this->documentsList,
            $this->logger,
            $this->config,
            $this->progress,
            $this->source,
            $this->destination,
            $this->mapFactory
        );
        $this->assertTrue($integrity->perform());
    }
}
