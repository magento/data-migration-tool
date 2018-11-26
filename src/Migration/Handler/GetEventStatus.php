<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Handler to transform catalog event status based on start and end dates
 */
class GetEventStatus extends AbstractHandler implements HandlerInterface
{
    const EVENT_OPEN = 0;

    const EVENT_UPCOMING = 1;

    const EVENT_CLOSED = 2;

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $dates = [
            'start' => $recordToHandle->getValue('date_start'),
            'end' =>$recordToHandle->getValue('date_end')
        ];
        $currentDate = date('Y-m-d H:i:s');
        if (($dates['start'] <= $currentDate) && ($dates['end'] > $currentDate)) {
            $status = self::EVENT_OPEN;
        } elseif (($dates['start'] > $currentDate) && ($dates['end'] > $currentDate)) {
            $status = self::EVENT_UPCOMING;
        } else {
            $status = self::EVENT_CLOSED;
        }
        $recordToHandle->setValue($this->field, $status);
    }
}
