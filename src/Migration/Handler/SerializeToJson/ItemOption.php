<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Handler\SerializeToJson;

use Migration\ResourceModel\Record;
use Migration\Exception;
use Migration\Handler\AbstractHandler;

/**
 * Handler to transform item option
 */
class ItemOption extends AbstractHandler
{
    const ITEM_OPTION_FIELD = 'code';
    const ITEM_OPTION_FORMAT = '/option_\d+/';

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            if ($this->shouldProcessField($recordToHandle->getData()[self::ITEM_OPTION_FIELD])) {
                $unserializeData = @unserialize($value);
                $value = (false === $unserializeData) ? $value : json_encode($unserializeData);
            }
        }
        $recordToHandle->setValue($this->field, $value);
    }

    /**
     * @inheritdoc
     */
    public function validate(Record $record)
    {
        parent::validate($record);
        if (!isset($record->getData()[self::ITEM_OPTION_FIELD])) {
            throw new Exception(sprintf("Field %s not found in the record.", self::ITEM_OPTION_FIELD));
        }
    }

    /**
     * Should process field
     *
     * @param string $itemOptionValue
     * @return bool
     */
    protected function shouldProcessField($itemOptionValue)
    {
        preg_match(self::ITEM_OPTION_FORMAT, $itemOptionValue, $matches);
        return (bool)$matches;
    }
}
