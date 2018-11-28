<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Magento\Indexer\Model\Processor\Handler;
use Migration\ResourceModel\Record;

/**
 * Class GetDestinationValueTest
 */
class GetEventStatusTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     * @dataProvider eventDatesDataProvider
     * @param string $dateStart
     * @param string $dateEnd
     * @param int $status
     */
    public function testHandle($dateStart, $dateEnd, $status)
    {
        /** @var \Migration\ResourceModel\Record|\PHPUnit_Framework_MockObject_MockObject $record */
        $record = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->setMethods(['setValue', 'getValue', 'getFields'])
            ->getMock();
        $record->expects($this->any())->method('getFields')->willReturn(['status']);
        $record->expects($this->any())->method('getValue')->willReturnMap(
            [
                ['date_start', $dateStart],
                ['date_end', $dateEnd]
            ]
        );
        $record->expects($this->once())->method('setValue')->with('status', $status);

        $record2 = $this->getMockBuilder(\Migration\ResourceModel\Record::class)
            ->disableOriginalConstructor()
            ->getMock();

        $handler = new GetEventStatus();
        $handler->setField('status');
        $handler->handle($record, $record2);
    }

    /**
     * @return array
     */
    public function eventDatesDataProvider()
    {
        return [
            'closed' => [
                'date_start' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'date_end' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'status' => GetEventStatus::EVENT_CLOSED
            ],
            'open' => [
                'date_start' => date('Y-m-d H:i:s', strtotime('-1 days')),
                'date_end' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'status' => GetEventStatus::EVENT_OPEN

            ],
            'upcoming' => [
                'date_start' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'date_end' => date('Y-m-d H:i:s', strtotime('+5 days')),
                'status' => GetEventStatus::EVENT_UPCOMING
            ]
        ];
    }
}
