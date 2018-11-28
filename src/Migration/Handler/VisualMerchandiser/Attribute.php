<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\VisualMerchandiser;

use Migration\ResourceModel\Record;
use Migration\Handler\AbstractHandler;

/**
 * Class Attribute
 */
class Attribute extends AbstractHandler
{
    const DESTINATION_DEFAULT_SKU_ATTRIBUTE = "SKU";

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        $valueArr = explode(',', $value);
        if (!array_search(strtolower(self::DESTINATION_DEFAULT_SKU_ATTRIBUTE), array_map('strtolower', $valueArr))) {
            array_push($valueArr, self::DESTINATION_DEFAULT_SKU_ATTRIBUTE);
        }
        $recordToHandle->setValue($this->field, implode(',', $valueArr));
    }
}
