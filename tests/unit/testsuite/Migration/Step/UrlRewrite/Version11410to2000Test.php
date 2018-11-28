<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\UrlRewrite;

use Migration\Step\UrlRewrite\Model\Version11410to2000\ProductRewritesWithoutCategories;
use Migration\Step\UrlRewrite\Model\Version11410to2000\ProductRewritesIncludedIntoCategories;

/**
 * Class UrlRewriteTest
 * @SuppressWarnings(PHPMD)
 */
class Version11410to2000Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\Step\UrlRewrite\Version11410to2000
     */
    protected $version;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ResourceModel\Record\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordCollectionFactory;

    /**
     * @var \Migration\ResourceModel\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $recordFactory;

    /**
     * @var \Migration\Step\UrlRewrite\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var ProductRewritesWithoutCategories|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRewritesWithoutCategories;

    /**
     * @var ProductRewritesIncludedIntoCategories|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRewritesIncludedIntoCategories;

    /**
     * @var Suffix|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $suffix;

    /**
     * @var TemporaryTable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $temporaryTable;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'finish', 'advance']
        );
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['debug', 'error']
        );
        $this->config = $this->createMock(\Migration\Config::class);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => 'database',
            'version' => '1.14.1.0'
        ]);
        $this->source = $this->createMock(\Migration\ResourceModel\Source::class);
        $this->destination = $this->createMock(\Migration\ResourceModel\Destination::class);
        $this->recordCollectionFactory = $this->createPartialMock(
            \Migration\ResourceModel\Record\CollectionFactory::class,
            ['create']
        );
        $this->recordFactory = $this->createPartialMock(
            \Migration\ResourceModel\RecordFactory::class,
            ['create']
        );
        $this->helper = $this->getMockBuilder(\Migration\Step\UrlRewrite\Helper::class)
            ->disableOriginalConstructor()
            ->setMethods(['processFields'])
            ->getMock();
        $this->productRewritesWithoutCategories = $this->getMockBuilder(ProductRewritesWithoutCategories::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productRewritesIncludedIntoCategories =
            $this->getMockBuilder(ProductRewritesIncludedIntoCategories::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->suffix = $this->createMock(\Migration\Step\UrlRewrite\Model\Suffix::class);
        $this->temporaryTable = $this->createMock(\Migration\Step\UrlRewrite\Model\TemporaryTable::class);
    }

    /**
     * @return void
     */
    public function testIntegrity()
    {
        $this->helper->expects($this->any())->method('processFields')->willReturn([
            'array' => 'with_processed_fields'
        ]);
        $this->version = new \Migration\Step\UrlRewrite\Version11410to2000(
            $this->progress,
            $this->logger,
            $this->config,
            $this->source,
            $this->destination,
            $this->recordCollectionFactory,
            $this->recordFactory,
            $this->helper,
            $this->productRewritesWithoutCategories,
            $this->productRewritesIncludedIntoCategories,
            $this->suffix,
            $this->temporaryTable,
            'integrity'
        );
        $this->assertTrue(true);
    }
}
