<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var null
     */
    protected $isIpPacked = null;

    /**
     * @param int $isIpPacked
     */
    public function __construct($isIpPacked = 1)
    {
        $this->isIpPacked = $isIpPacked;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);

        $value = $recordToHandle->getValue($this->field);
        if (!$value) {
            $recordToHandle->setValue($this->field, 0);
            return;
        }
        $newValue = $this->isIpPacked ? ip2long(inet_ntop($value)) : ip2long($value);
        $recordToHandle->setValue($this->field, $newValue);
    }
}
