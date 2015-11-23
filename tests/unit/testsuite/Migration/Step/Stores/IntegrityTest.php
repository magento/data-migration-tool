<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\RecordTransformerFactory;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;

/**
 * Class Integrity
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Integrity
     */
    protected $integrity;
    /**
     * @var \Migration\ResourceModel\Source|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $source;

    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @var \Migration\Step\Stores\Helper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->progress = $this->getMockBuilder('Migration\App\ProgressBar\LogLevelProcessor')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->source = $this->getMockBuilder('Migration\ResourceModel\Source')
            ->disableOriginalConstructor()
            ->getMock();
        $this->destination = $this->getMockBuilder('Migration\ResourceModel\Destination')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->helper = $this->getMockBuilder('Migration\Step\Stores\Helper')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $document = $this->getMockBuilder('Migration\ResourceModel\Document')->disableOriginalConstructor()->getMock();

        $this->progress->expects($this->once())->method('start')->with('3');
        $this->progress->expects($this->any())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->source->expects($this->any())->method('getDocument', 'getRecords')->willReturn($document);
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);
        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([
                'core_store' => 'store',
                'core_store_group' => 'store_group',
                'core_website' => 'store_website'
            ]);

        $this->integrity = new Integrity(
            $this->progress,
            $this->source,
            $this->destination,
            $this->helper
        );
        $this->assertTrue($this->integrity->perform());
    }
}
