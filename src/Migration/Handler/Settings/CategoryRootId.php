<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Settings;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;

/**
 * Handler to convert category root id
 */
class CategoryRootId extends AbstractHandler
{
    const SOURCE_DEFAULT_CATEGORY_ID = 2;

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if ($value == self::SOURCE_DEFAULT_CATEGORY_ID) {
            $recordToHandle->setValue($this->field, null);
        }
    }
}
