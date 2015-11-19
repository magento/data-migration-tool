<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\VisualMerchandiser\Handler;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;

class SmartAttribute extends AbstractHandler
{
    const ATTRIBUTE_CODE_NAME = 'attribute_codes';
    const ATTRIBUTE_VALUE_NAME = 'smart_attributes';

    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $count = 0;
        $attributes = [];
        $this->validate($recordToHandle);
        $attributeCode = $recordToHandle->getValue(self::ATTRIBUTE_CODE_NAME);
        $attributeCodeArr = explode(',', $attributeCode);
        $attributeValues = unserialize($recordToHandle->getValue(self::ATTRIBUTE_VALUE_NAME));
        if (is_array($attributeValues)) {
            foreach ($attributeValues as $attributeValue) {
                $attribute = $this->parseOperator($attributeValue['value']);
                $attribute['attribute'] = $attributeCodeArr[$count];
                $attribute['logic'] = $attributeValue['link'];
                $count++;
                $attributes[] = $attribute;
            }
            $attributeString = \Zend_Json::encode($attributes);

            $recordToHandle->setValue($this->field, $attributeString);
        }
    }

    protected function parseOperator($attribute)
    {
        $response = [];
        $possibleValues = [
            'gte'   => '>=',
            'lte'   => '<=',
            'eq'    => '=',
            'neq'   => '!',
            'gt'    => '>',
            'lt'    => '<',
            'like'  => '*'
        ];
        foreach ($possibleValues as $operator => $value) {
            if (strpos($attribute, $value) !== false) {
                $response['operator'] = $operator;
                $response['value'] = trim(str_replace($value, "", $attribute));
            }
        }
        if (empty($response)) {
            $response['operator'] = 'eq';
            $response['value'] = $attribute;
        }

        return $response;
    }
}