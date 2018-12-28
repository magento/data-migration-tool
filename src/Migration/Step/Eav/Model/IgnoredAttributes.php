<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Model;

use Migration\Step\Eav\Helper;
use Migration\Step\Eav\InitialData;

/**
 * Class IgnoredAttributes
 */
class IgnoredAttributes
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var InitialData
     */
    protected $initialData;

    /**
     * @param Helper $helper
     * @param InitialData $initialData
     */
    public function __construct(
        Helper $helper,
        InitialData $initialData
    ) {
        $this->helper = $helper;
        $this->initialData = $initialData;
    }

    /**
     * Remove ignored attributes from source records
     *
     * @param array $sourceRecords
     * @return array
     */
    public function clearIgnoredAttributes($sourceRecords)
    {
        $attributesIgnoreIds = [];
        $initialAttributes = $this->initialData->getAttributes('source');
        $ignoredAttributes = $this->helper->getAttributesGroupCodes('ignore');
        foreach ($ignoredAttributes as $attributeCode => $entityTypeIds) {
            foreach ($initialAttributes as $attribute) {
                if ($attribute['attribute_code'] == $attributeCode
                    && in_array($attribute['entity_type_id'], $entityTypeIds)
                ) {
                    $attributesIgnoreIds[] = $attribute['attribute_id'];
                }
            }
        }
        foreach ($sourceRecords as $attrNum => $sourceAttribute) {
            if (isset($sourceAttribute['attribute_id'])
                && in_array($sourceAttribute['attribute_id'], $attributesIgnoreIds)
            ) {
                unset($sourceRecords[$attrNum]);
            }
        }
        return $sourceRecords;
    }
}
