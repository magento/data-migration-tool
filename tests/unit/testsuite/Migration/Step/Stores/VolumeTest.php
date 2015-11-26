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
 * Class VolumeTest
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Volume
     */
    protected $volume;
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
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

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
        $this->logger = $this->getMock('Migration\Logger\Logger', ['addRecord'], [], '', false);
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $fields = ['field_name' => []];

        $structure = $this->getMockBuilder('Migration\ResourceModel\Structure')->disableOriginalConstructor()
            ->setMethods(['getFields'])->getMock();
        $structure->expects($this->any())->method('getFields')->will($this->returnValue($fields));
        $document = $this->getMockBuilder('Migration\ResourceModel\Document')->disableOriginalConstructor()
            ->setMethods(['getStructure'])
            ->getMock();

        $this->progress->expects($this->once())->method('start')->with('3');
        $this->progress->expects($this->any())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $document->expects($this->any())->method('getStructure')->willReturn($structure);
        $this->source->expects($this->any())->method('getDocument')->willReturn($document);
        $this->destination->expects($this->any())->method('getDocument')->willReturn($document);
        $this->source->expects($this->any())->method('getRecordsCount')->with()->willReturn(1);
        $this->destination->expects($this->any())->method('getRecordsCount')->with()->willReturn(1);
        $this->helper->expects($this->any())->method('getDocumentList')
            ->willReturn([
                'core_store' => 'store',
                'core_store_group' => 'store_group',
                'core_website' => 'store_website'
            ]);

        $this->volume = new Volume(
            $this->progress,
            $this->source,
            $this->destination,
            $this->helper,
            $this->logger
        );
        $this->assertTrue($this->volume->perform());
    }
}
