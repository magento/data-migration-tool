<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\VisualMerchandiser;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;

/**
 * Class SmartAttribute
 */
class SmartAttribute extends AbstractHandler
{
    const ATTRIBUTE_CODE_NAME = 'attribute_codes';
    const ATTRIBUTE_VALUE_NAME = 'smart_attributes';

    /**
     * @inheritdoc
     */
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

    /**
     * Parse operator
     *
     * @param string $attribute
     * @return array
     */
    protected function parseOperator($attribute)
    {
        $result = [];
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
                $result['operator'] = $operator;
                $result['value'] = trim(str_replace($value, "", $attribute));
            }
        }
        if (empty($result)) {
            $result['operator'] = 'eq';
            $result['value'] = $attribute;
        }

        return $result;
    }
}
