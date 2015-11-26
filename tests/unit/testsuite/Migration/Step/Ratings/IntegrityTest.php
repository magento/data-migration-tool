<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Integrity
     */
    protected $integrity;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\ResourceModel\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    /**
     * @var \Migration\ResourceModel\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $document;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->destination = $this->getMock(
            'Migration\ResourceModel\Destination',
            ['getAdapter', 'getDocumentList', 'getDocument', 'addDocumentPrefix'],
            [],
            '',
            false
        );
        $this->destination
            ->expects($this->any())
            ->method('addDocumentPrefix')
            ->will($this->returnValueMap([['rating_store', 'rating_store'], ['rating', 'rating']]));

        $this->structure = $this->getMock('Migration\ResourceModel\Structure', ['getFields'], [], '', false);
        $this->document = $this->getMock('Migration\ResourceModel\Document', ['getStructure'], [], '', false);
        $this->logger = $this->getMock('Migration\Logger\Logger', ['warning', 'error'], [], '', false);
        $this->progress = $this->getMock(
            'Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'advance', 'finish'],
            [],
            '',
            false
        );
    }

    /**
     * @return void
     */
    public function testPerform()
    {
        $this->integrity = new Integrity($this->destination, $this->logger, $this->progress);
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['rating', 'rating_store']);
        $this->structure->expects($this->once())->method('getFields')->willReturn(['is_active' => []]);
        $this->document->expects($this->once())->method('getStructure')->willReturn($this->structure);
        $this->destination->expects($this->once())->method('getDocument')->with('rating')->willReturn($this->document);
        $this->assertTrue($this->integrity->perform());
    }

    /**
     * @return void
     */
    public function testPerformDocumentsFail()
    {
        $this->integrity = new Integrity($this->destination, $this->logger, $this->progress);
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->never())->method('finish');
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn([]);
        $this->structure->expects($this->never())->method('getFields');
        $this->document->expects($this->never())->method('getStructure');
        $this->destination->expects($this->never())->method('getDocument');
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                '"rating" or "rating_store" documents do not exist in the destination resource'
            );
        $this->assertFalse($this->integrity->perform());
    }

    /**
     * @return void
     */
    public function testPerformFieldFail()
    {
        $this->integrity = new Integrity($this->destination, $this->logger, $this->progress);
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->never())->method('finish');
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['rating', 'rating_store']);
        $this->structure->expects($this->once())->method('getFields')->willReturn(['field' => []]);
        $this->document->expects($this->once())->method('getStructure')->willReturn($this->structure);
        $this->destination->expects($this->once())->method('getDocument')->with('rating')->willReturn($this->document);
        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                '"is_active" field does not exist in "rating" document of the destination resource'
            );
        $this->assertFalse($this->integrity->perform());
    }
}
