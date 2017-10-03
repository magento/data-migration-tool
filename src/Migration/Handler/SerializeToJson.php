<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to transform field according to the map
 */
class SerializeToJson extends AbstractHandler
{
    /**
     * Sometimes fields has a broken serialize data, for example enterprise_logging_event_changes.result_data.
     * If property sets to true, ignore all notices from unserialize()
     *
     * @var bool
     *
     */
    protected $ignoreBrokenData;

    public function __construct($ignoreBrokenData = false)
    {
        $this->ignoreBrokenData = (bool)$ignoreBrokenData;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            $unserializeData = $this->ignoreBrokenData ? @unserialize($value) : unserialize($value);
            $value = false === $unserializeData ? json_encode([]) : json_encode($unserializeData);
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
