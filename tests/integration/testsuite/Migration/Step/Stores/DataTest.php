<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

/**
 * Class DataTest
 * @dbFixture stores
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
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
    protected $source;
    protected $recordFactory;
    protected $helper;

    public function setUp()
    {
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get('\Migration\Config')
            ->init(dirname(__DIR__) . '/../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $this->progress = $objectManager->create('Migration\App\ProgressBar\LogLevelProcessor');
        $this->source = $objectManager->create('Migration\Resource\Source');
        $this->destination = $objectManager->create('Migration\Resource\Destination');
        $this->recordFactory = $objectManager->create('Migration\Resource\RecordFactory');
        $this->helper = $objectManager->create('Migration\Step\Stores\Helper');
    }

    public function testPerform()
    {
        $data = new Data(
            $this->progress,
            $this->source,
            $this->destination,
            $this->recordFactory,
            $this->helper
        );
        $this->assertTrue($data->perform());
        foreach ($this->destinationDocuments as $documentName => $recordsCount) {
            $this->assertEquals($recordsCount, count($this->destination->getRecords($documentName, 0)));
        }
    }
}
