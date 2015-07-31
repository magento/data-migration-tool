<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

class RatingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\Resource\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Ratings
     */
    protected $ratings;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\Resource\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Migration\Resource\Structure|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $structure;

    /**
     * @var \Migration\Resource\Document|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $document;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Migration\App\ProgressBar\LogLevelProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $progress;

    public function setUp()
    {
        $this->destination = $this->getMock(
            'Migration\Resource\Destination',
            ['getAdapter', 'getDocumentList', 'getDocument', 'addDocumentPrefix'],
            [],
            '',
            false
        );
        $this->destination
            ->expects($this->any())
            ->method('addDocumentPrefix')
            ->will($this->returnValueMap([['rating_store', 'rating_store'], ['rating', 'rating']]));
        $this->select = $this->getMock('Magento\Framework\DB\Select', ['from', 'where'], [], '', false);
        $this->adapter = $this->getMock(
            'Migration\Resource\Adapter\Mysql',
            ['getSelect', 'loadDataFromSelect', 'updateDocument'],
            [],
            '',
            false
        );
        $this->structure = $this->getMock('Migration\Resource\Structure', ['getFields'], [], '', false);
        $this->document = $this->getMock('Migration\Resource\Document', ['getStructure'], [], '', false);
        $this->logger = $this->getMock('Migration\Logger\Logger', ['warning', 'error'], [], '', false);
        $this->progress = $this->getMock(
            'Migration\App\ProgressBar\LogLevelProcessor',
            ['start', 'advance', 'finish'],
            [],
            '',
            false
        );
    }

    public function testIntegrity()
    {
        $this->ratings = new Ratings($this->destination, $this->logger, $this->progress, 'integrity');
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->destination->expects($this->once())->method('getDocumentList')->willReturn(['rating', 'rating_store']);
        $this->structure->expects($this->once())->method('getFields')->willReturn(['is_active' => []]);
        $this->document->expects($this->once())->method('getStructure')->willReturn($this->structure);
        $this->destination->expects($this->once())->method('getDocument')->with('rating')->willReturn($this->document);
        $this->assertTrue($this->ratings->perform());
    }

    public function testIntegrityDocumentsFail()
    {
        $this->ratings = new Ratings($this->destination, $this->logger, $this->progress, 'integrity');
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
                'Integrity check failed due to "rating" or "rating_store" documents do not exist in the'
                . ' destination resource'
            );
        $this->assertFalse($this->ratings->perform());
    }

    public function testIntegrityFieldFail()
    {
        $this->ratings = new Ratings($this->destination, $this->logger, $this->progress, 'integrity');
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
                'Integrity check failed due to "is_active" field does not exist in "rating" document of '
                . 'the destination resource'
            );
        $this->assertFalse($this->ratings->perform());
    }

    public function testData()
    {
        $this->ratings = new Ratings($this->destination, $this->logger, $this->progress, 'data');
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->destination->expects($this->once())->method('getAdapter')->willReturn($this->adapter);
        $this->adapter->expects($this->once())->method('getSelect')->willReturn($this->select);
        $this->select
            ->expects($this->once())
            ->method('from')
            ->with('rating_store', ['rating_id'])
            ->will($this->returnSelf());
        $this->select
            ->expects($this->once())
            ->method('where')
            ->with('store_id > 0')
            ->will($this->returnSelf());
        $this->adapter
            ->expects($this->once())
            ->method('loadDataFromSelect')
            ->with($this->select)
            ->willReturn([['rating_id' => 1]]);
        $this->adapter
            ->expects($this->once())
            ->method('updateDocument')
            ->with('rating', ['is_active' => 1], 'rating_id IN (1)');
        $this->ratings->perform();
    }

    public function testVolumeCheck()
    {
        $this->ratings = new Ratings($this->destination, $this->logger, $this->progress, 'volume');
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->destination->expects($this->once())->method('getAdapter')->willReturn($this->adapter);
        $this->adapter->expects($this->exactly(2))->method('getSelect')->willReturn($this->select);
        $this->select
            ->expects($this->at(0))
            ->method('from')
            ->with('rating_store', ['rating_id'])
            ->will($this->returnSelf());
        $this->select
            ->expects($this->at(1))
            ->method('where')
            ->with('store_id > 0')
            ->will($this->returnSelf());
        $this->adapter
            ->expects($this->exactly(2))
            ->method('loadDataFromSelect')
            ->with($this->select)->willReturn([['rating_id' => 1]]);
        $this->select->expects($this->at(2))->method('from')->with('rating', ['rating_id'])->will($this->returnSelf());
        $this->select->expects($this->at(3))->method('where')->with('is_active = ?', 1)->will($this->returnSelf());
        $this->logger->expects($this->never())->method('error');
        $this->assertTrue($this->ratings->perform());
    }

    public function testVolumeCheckFailed()
    {
        $this->ratings = new Ratings($this->destination, $this->logger, $this->progress, 'volume');
        $this->progress->expects($this->once())->method('start')->with(1);
        $this->progress->expects($this->once())->method('advance');
        $this->progress->expects($this->once())->method('finish');
        $this->destination->expects($this->once())->method('getAdapter')->willReturn($this->adapter);
        $this->adapter->expects($this->exactly(2))->method('getSelect')->willReturn($this->select);
        $this->select
            ->expects($this->at(0))
            ->method('from')
            ->with('rating_store', ['rating_id'])
            ->will($this->returnSelf());
        $this->select
            ->expects($this->at(1))
            ->method('where')
            ->with('store_id > 0')
            ->will($this->returnSelf());
        $this->adapter
            ->expects($this->at(1))
            ->method('loadDataFromSelect')
            ->with($this->select)
            ->willReturn([['rating_id' => 1]]);
        $this->adapter
            ->expects($this->at(3))
            ->method('loadDataFromSelect')
            ->with($this->select)
            ->willReturn([['rating_id' => 2]]);
        $this->select
            ->expects($this->at(2))
            ->method('from')
            ->with('rating', ['rating_id'])
            ->will($this->returnSelf());
        $this->select
            ->expects($this->at(3))
            ->method('where')
            ->with('is_active = ?', 1)
            ->will($this->returnSelf());
        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Mismatch of entities in the documents: rating, rating_store');
        $this->assertFalse($this->ratings->perform());
    }
}
