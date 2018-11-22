<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

/**
 * Class DataTest
 * @dbFixture stores
 */
class DataTest extends \PHPUnit\Framework\TestCase
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
     * @var \Migration\ResourceModel\RecordFactory
     */
    private $recordFactory;

    /**
     * @var \Migration\Step\Stores\Model\DocumentsList
     */
    private $documentsList;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    private $recordTransformerFactory;

    /**
     * @var \Migration\Reader\MapFactory
     */
    private $map;

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
        $this->recordFactory = $objectManager->create(\Migration\ResourceModel\RecordFactory::class);
        $this->documentsList = $objectManager->create(\Migration\Step\Stores\Model\DocumentsList::class);
        $this->recordTransformerFactory = $objectManager->create(\Migration\RecordTransformerFactory::class);
        $this->map = $objectManager->create(\Migration\Reader\MapFactory::class);
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
            $this->documentsList,
            $this->recordTransformerFactory,
            $this->map
        );
        $this->assertTrue($data->perform());
        foreach ($this->destinationDocuments as $documentName => $recordsCount) {
            $this->assertEquals($recordsCount, count($this->destination->getRecords($documentName, 0)));
        }
    }
}
