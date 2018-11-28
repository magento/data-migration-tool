<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to convert Ip with different formats
 */
class ConvertIp extends AbstractHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);

        $value = $recordToHandle->getValue($this->field);
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            || filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
        ) {
            $newValue = (int)ip2long($value);
        } else if (@inet_ntop($value) !== false) {
            $newValue = (int)ip2long(inet_ntop($value));
        } else {
            $newValue = 0;
        }

        $recordToHandle->setValue($this->field, $newValue);
    }
}
