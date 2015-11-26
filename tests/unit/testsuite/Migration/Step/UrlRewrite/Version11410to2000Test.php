<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

/**
 * Class UrlRewriteTest
 */
class Version11410to2000Test extends \PHPUnit_Framework_TestCase
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
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->getMock(
            '\Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'finish', 'advance'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock('\Migration\Logger\Logger', ['debug', 'error'], [], '', false);
        $this->config = $this->getMock('\Migration\Config', [], [], '', false);
        $this->config->expects($this->any())->method('getSource')->willReturn([
            'type' => 'database',
            'version' => '1.14.1.0'
        ]);
        $this->source = $this->getMock('\Migration\ResourceModel\Source', [], [], '', false);
        $this->destination = $this->getMock('\Migration\ResourceModel\Destination', [], [], '', false);
        $this->recordCollectionFactory = $this->getMock(
            '\Migration\ResourceModel\Record\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->recordFactory = $this->getMock('\Migration\ResourceModel\RecordFactory', ['create'], [], '', false);
    }

    /**
     * @return void
     */
    public function testIntegrity()
    {
        $this->version = new \Migration\Step\UrlRewrite\Version11410to2000(
            $this->progress,
            $this->logger,
            $this->config,
            $this->source,
            $this->destination,
            $this->recordCollectionFactory,
            $this->recordFactory,
            'integrity'
        );
    }
}
