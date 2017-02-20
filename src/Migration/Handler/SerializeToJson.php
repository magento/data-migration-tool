<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Handler to transform field according to the map
 */
class SerializeToJson extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $map = [];

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if ((preg_match('/^[OC]:\d{1,}:"[a-zA-Z0-9_]{1,}"/', $value))) {
            $newValue = json_encode(unserialize($value));
        } else {
            $newValue = json_encode($value);
        }
        $recordToHandle->setValue($this->field, $newValue);
    }
}
