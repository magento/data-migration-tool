<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Integrity;

use Migration\Step\Eav\Helper;
use Migration\Reader\MapInterface;

/**
 * Class AttributeFrontendInput
 */
class AttributeFrontendInput
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var string
     */
    private $attributeDocument = 'eav_attribute';

    /**
     * @var string
     */
    private $attributeFieldName = 'frontend_input';

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Check product attribute sets contain all required attribute group names
     *
     * @return array
     */
    public function checkAttributeFrontendInput()
    {
        $sourceAttributes = $this->helper->getSourceRecords($this->attributeDocument);
        $sourceAttributes = $this->helper->clearIgnoredAttributes($sourceAttributes);
        $emptyAttributes = $this->getFrontendInputEmptyAttributes($sourceAttributes);

        $incompatibleData = [];
        foreach ($emptyAttributes as $emptyAttribute) {
            $incompatibleData[] = [
                'document' => $this->attributeDocument,
                'field' => $this->attributeFieldName,
                'error' => sprintf(
                    'Attribute with attribute_id=%s cannot contain empty field value',
                    $emptyAttribute['attribute_id']
                )
            ];
        }
        return $incompatibleData;
    }

    /**
     * Retrieves attributes with empty frontend_input parameter
     *
     * @param array $records
     * @return array
     */
    private function getFrontendInputEmptyAttributes($records)
    {
        $result = [];
        $filterGroupCodes = $this->helper->getAttributesGroupCodes('frontend_input_empty_allowed');
        foreach ($records as $record) {
            if (
                empty($record['frontend_input']) &&
                (
                    !array_key_exists($record['attribute_code'], $filterGroupCodes) ||
                    !in_array($record['entity_type_id'], $filterGroupCodes[$record['attribute_code']])
                )
            ) {
                $result[] = $record;
            }
        }
        return $result;
    }
}
