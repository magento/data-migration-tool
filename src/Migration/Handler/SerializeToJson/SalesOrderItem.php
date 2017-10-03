<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\SerializeToJson;

use Migration\ResourceModel\Record;
use Migration\Exception;
use Migration\Handler\AbstractHandler;

/**
 * Handler to transform field from sales_order_item
 * @SuppressWarnings(CyclomaticComplexity)
 */
class SalesOrderItem extends AbstractHandler
{
    /**
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $value = $recordToHandle->getValue($this->field);
        if (null !== $value) {
            $value = $value ? unserialize($value) : $value;
            if (isset($value['options'])) {
                foreach ($value['options'] as $key => $option) {
                    if (array_key_exists('option_type', $option) && $option['option_type'] === 'file') {
                        $optionValue = $option['option_value'] ? unserialize($option['option_value']) :
                            $option['option_value'];
                        $value['options'][$key]['option_value'] = json_encode($optionValue);
                    }
                }
            }
            if (isset($value['bundle_selection_attributes'])) {
                $bundleSelectionAttributes = $value['bundle_selection_attributes'] ?
                    unserialize($value['bundle_selection_attributes']) :
                    $value['bundle_selection_attributes'];
                $value['bundle_selection_attributes'] = json_encode($bundleSelectionAttributes);
            }
            $value = $value ? json_encode($value) : $value;
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
