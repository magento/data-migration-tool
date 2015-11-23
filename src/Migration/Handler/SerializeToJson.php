<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $newValue = json_encode(unserialize($value));
        $recordToHandle->setValue($this->field, $newValue);
    }
}
