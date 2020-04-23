<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to format timestamp to date
 */
class ConvertTimestampToDate extends AbstractHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if ($value && is_numeric($value) && $recordToHandle->getValue('frontend_input') == 'date') {
            $date = new \DateTime();
            $date->setTimestamp($value);
            $newValue = $date->format('Y-m-d H:i:s');
            $recordToHandle->setValue($this->field, $newValue);
        }
    }
}
