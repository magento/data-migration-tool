<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Ratings;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Migration\ResourceModel\Destination|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $destination;

    /**
     * @var Data
     */
    protected $data;

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
        $this->destination = $this->createPartialMock(
            \Migration\ResourceModel\Destination::class,
            ['getAdapter', 'getDocumentList', 'getDocument', 'addDocumentPrefix']
        );
        $this->destination
            ->expects($this->any())
            ->method('addDocumentPrefix')
            ->will($this->returnValueMap([['rating_store', 'rating_store'], ['rating', 'rating']]));
        $this->select = $this->createPartialMock(
            \Magento\Framework\DB\Select::class,
            ['from', 'where']
        );
        $this->adapter = $this->createPartialMock(
            \Migration\ResourceModel\Adapter\Mysql::class,
            ['getSelect', 'loadDataFromSelect', 'updateDocument']
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
        $this->data = new Data($this->destination, $this->progress);
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
        $this->data->perform();
    }
}
