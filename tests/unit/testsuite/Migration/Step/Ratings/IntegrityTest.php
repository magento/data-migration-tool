<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

/**
 * Class IntegrityTest
 */
class IntegrityTest extends \PHPUnit\Framework\TestCase
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
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getAdapter', 'getDocumentList', 'getDocument', 'addDocumentPrefix']
        );
        $this->destination
            ->expects($this->any())
            ->method('addDocumentPrefix')
            ->will($this->returnValueMap([['rating_store', 'rating_store'], ['rating', 'rating']]));

        $this->structure = $this->createPartialMock(
            \Migration\ResourceModel\Structure::class,
            ['getFields']
        );
        $this->document = $this->createPartialMock(
            \Migration\ResourceModel\Document::class,
            ['getStructure']
        );
        $this->logger = $this->createPartialMock(
            \Migration\Logger\Logger::class,
            ['warning', 'error']
        );
        $this->progress = $this->createPartialMock(
            \Migration\App\ProgressBar\LogLevelProcessor::class,
            ['start', 'advance', 'finish']
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
