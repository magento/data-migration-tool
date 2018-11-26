<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Integrity;

use Migration\Model\Eav\AttributeGroupNameToCodeMap;
use Migration\Step\Eav\Helper;

/**
 * Class AttributeGroupNames
 */
class AttributeGroupNames
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var AttributeGroupNameToCodeMap
     */
    private $groupNameToCodeMap;

    /**
     * @var string
     */
    private $attributeGroupDocument = 'eav_attribute_group';

    /**
     * @var string
     */
    private $attributeGroupFieldName = 'attribute_group_name';

    /**
     * @param Helper $helper
     * @param AttributeGroupNameToCodeMap $groupNameToCodeMap
     */
    public function __construct(
        Helper $helper,
        AttributeGroupNameToCodeMap $groupNameToCodeMap
    ) {
        $this->helper = $helper;
        $this->groupNameToCodeMap = $groupNameToCodeMap;
    }

    /**
     * Check product attribute sets contain all required attribute group names
     *
     * @return array
     */
    public function checkAttributeGroupNames()
    {
        $attributeGroups = $this->helper->getSourceRecords($this->attributeGroupDocument, ['attribute_group_id']);
        $entityTypeIdCatalogProduct = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_code'])
        ['catalog_product']['entity_type_id'];
        $attributeSetsOfCatalogProduct = [];
        $attributeSets = $this->helper->getSourceRecords('eav_attribute_set', ['attribute_set_id']);
        foreach ($attributeSets as $attributeSet) {
            if ($attributeSet['entity_type_id'] == $entityTypeIdCatalogProduct) {
                $attributeSetsOfCatalogProduct[$attributeSet['attribute_set_id']] =
                    $attributeSet['attribute_set_name'];
            }
        }
        $attributeGroupsOfCatalogProduct = [];
        foreach ($attributeGroups as $group) {
            if (in_array($group['attribute_set_id'], array_keys($attributeSetsOfCatalogProduct))) {
                $attributeGroupsOfCatalogProduct[$group['attribute_set_id']][] = $group[$this->attributeGroupFieldName];
            }
        }
        return $this->checkForErrors($attributeGroupsOfCatalogProduct, $attributeSetsOfCatalogProduct);
    }

    /**
     * Check for errors
     *
     * @param array $attributeGroupsOfCatalogProduct
     * @param array $attributeSetsOfCatalogProduct
     * @return array
     */
    protected function checkForErrors(array $attributeGroupsOfCatalogProduct, array $attributeSetsOfCatalogProduct)
    {
        $incompatibleDocumentFieldsData = [];
        $groupNamesToValidate = array_keys($this->groupNameToCodeMap->getMap('catalog_product'));
        foreach ($attributeGroupsOfCatalogProduct as $attributeSetId => $groupNames) {
            if (!empty(array_diff($groupNamesToValidate, $groupNames))) {
                $error = 'The product attribute set "%s" does not contain all required attribute group names "%s"';
                $error = sprintf(
                    $error,
                    $attributeSetsOfCatalogProduct[$attributeSetId],
                    implode(', ', $groupNamesToValidate)
                );
                $errorDetails['document'] = $this->attributeGroupDocument;
                $errorDetails['field'] = $this->attributeGroupFieldName;
                $errorDetails['error'] = $error;
                $incompatibleDocumentFieldsData[] = $errorDetails;
            }
        }
        return $incompatibleDocumentFieldsData;
    }
}
