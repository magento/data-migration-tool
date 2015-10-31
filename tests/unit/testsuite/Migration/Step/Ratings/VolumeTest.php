<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

use Migration\Logger\Logger;

/**
 * Class VolumeTest
 */
class VolumeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Volume
     */
    protected $volume;

    /**
     * @var \Migration\Logger\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Migration\ResourceModel\Adapter\Mysql|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

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
        $this->select = $this->getMock('Magento\Framework\DB\Select', ['from', 'where'], [], '', false);
        $this->adapter = $this->getMock(
            'Migration\ResourceModel\Adapter\Mysql',
            ['getSelect', 'loadDataFromSelect', 'updateDocument'],
            [],
            '',
            false
        );
        $this->logger = $this->getMock('Migration\Logger\Logger', ['addRecord'], [], '', false);
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
        $this->volume = new Volume($this->destination, $this->logger, $this->progress);
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
        $this->logger->expects($this->never())->method('addRecord');
        $this->assertTrue($this->volume->perform());
    }

    /**
     * @return void
     */
    public function testPerformFailed()
    {
        $this->volume = new Volume($this->destination, $this->logger, $this->progress);
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
            ->method('addRecord')
            ->with(Logger::ERROR, 'Mismatch of entities in the documents: rating, rating_store');
        $this->assertFalse($this->volume->perform());
    }
}
