<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Resource\Record;

/**
 * Handler to set hash value to the field, based on other field
 */
class ConvertBinaryIp extends AbstractHandler implements HandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);

        $value      = $recordToHandle->getValue($this->field);
        if (!$value) {
            return;
        }
        $newValue   = ip2long(inet_ntop($value));
        $recordToHandle->setValue($this->field, $newValue);
    }
}
