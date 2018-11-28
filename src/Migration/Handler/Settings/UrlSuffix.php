<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;

/**
 * Handler to convert url suffix
 */
class UrlSuffix extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if ($value && is_string($value) && substr($value, 0, 1) != '.') {
            $value = '.' . $value;
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
