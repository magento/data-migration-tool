<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step;

/**
 * Class CustomCustomerAttributesTest
 */
abstract class CustomCustomerAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Step\CustomCustomerAttributes
     */
    protected $step;

    /**
     * @var \Migration\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Migration\Resource\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\ProgressBar|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Resource\RecordFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factory;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    public function setUp()
    {
        $this->config = $this->getMockBuilder('Migration\Config')->disableOriginalConstructor()
            ->setMethods(['getSource'])
            ->getMock();
        $this->config->expects($this->any())->method('getSource')->will(
            $this->returnValue(['type' => DatabaseStage::SOURCE_TYPE])
        );

        $this->source = $this->getMockBuilder('Migration\Resource\Source')->disableOriginalConstructor()
            ->setMethods(['getDocument', 'getRecordsCount', 'getAdapter', 'addDocumentPrefix', 'getRecords'])
            ->getMock();
        $this->destination = $this->getMockBuilder('Migration\Resource\Destination')->disableOriginalConstructor()
            ->setMethods(['getDocument', 'getRecordsCount', 'getAdapter', 'addDocumentPrefix', 'saveRecords'])
            ->getMock();
        $this->progress = $this->getMockBuilder('Migration\ProgressBar')->disableOriginalConstructor()
            ->setMethods(['start', 'finish', 'advance'])
            ->getMock();
        $this->progress->expects($this->any())->method('start')->with(4);
        $this->progress->expects($this->any())->method('finish');
        $this->progress->expects($this->any())->method('advance');

        $this->factory = $this->getMockBuilder('Migration\Resource\RecordFactory')->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->logger = $this->getMockBuilder('Migration\Logger\Logger')->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }
}
