<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav\Model;

use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\Step\Eav\Helper;
use Migration\Step\Eav\InitialData;

/**
 * Class Data
 */
class Data
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var Destination
     */
    private $destination;

    /**
     * @var InitialData
     */
    private $initialData;

    /**
     * @var IgnoredAttributes
     */
    private $ignoredAttributes;

    /**
     * @var array
     */
    private $excludedProductAttributeGroups = [
        'General',
        'Prices',
        'Recurring Profile'
    ];

    const ATTRIBUTE_SETS_ALL = 'all';
    const ATTRIBUTE_SETS_DEFAULT = 'default';
    const ATTRIBUTE_SETS_NONE_DEFAULT = 'none_default';

    const ENTITY_TYPE_PRODUCT_CODE = 'catalog_product';
    const ENTITY_TYPE_CATEGORY_CODE = 'catalog_category';
    const ENTITY_TYPE_CUSTOMER_CODE = 'customer';
    const ENTITY_TYPE_CUSTOMER_ADDRESS_CODE = 'customer_address';

    const TYPE_SOURCE = 'source';
    const TYPE_DEST = 'destination';

    /**
     * @param Source $source
     * @param Destination $destination
     * @param Helper $helper
     * @param InitialData $initialData
     * @param IgnoredAttributes $ignoredAttributes
     */
    public function __construct(
        Source $source,
        Destination $destination,
        Helper $helper,
        InitialData $initialData,
        IgnoredAttributes $ignoredAttributes
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
        $this->initialData = $initialData;
        $this->ignoredAttributes = $ignoredAttributes;
    }

    /**
     * Update mapped keys for specific column in table
     *
     * @param string $destDocument
     * @param string $column
     * @param array $records
     * @param array $mappedIdKeys
     */
    public function updateMappedKeys($destDocument, $column, $records, $mappedIdKeys)
    {
        if (array_values($mappedIdKeys) == array_values(array_keys($mappedIdKeys))) {
            return;
        }
        foreach ($records as &$record) {
            if (empty($mappedIdKeys[$record[$column]])) {
                throw new \Migration\Exception(
                    sprintf('Not mapped id key %s found for %s.%s ', $record[$column], $destDocument, $column)
                );
            }
            $record[$column] = $mappedIdKeys[$record[$column]];
        }
        $this->destination->clearDocument($destDocument);
        $this->destination->saveRecords($destDocument, $records);
    }

    /**
     * Get list of product attribute sets
     *
     * @param string $mode
     * @param string $type
     * @return array|mixed
     */
    public function getProductAttributeSets(
        $mode = self::ATTRIBUTE_SETS_ALL,
        $type = self::TYPE_SOURCE
    ) {
        $productEntityTypeId = $this->getEntityTypeIdByCode(self::ENTITY_TYPE_PRODUCT_CODE, $type);
        $attributeSets = [];
        foreach ($this->initialData->getAttributeSets($type) as $attributeSet) {
            if ($productEntityTypeId == $attributeSet['entity_type_id']) {
                $attributeSets[$attributeSet['attribute_set_id']] = $attributeSet;
            }
        }
        if ($mode == self::ATTRIBUTE_SETS_DEFAULT) {
            return array_shift($attributeSets);
        } else if ($mode == self::ATTRIBUTE_SETS_NONE_DEFAULT) {
            array_shift($attributeSets);
            return $attributeSets;
        }
        return $attributeSets;
    }

    /**
     * Return entity type id by its code
     *
     * @param string $code
     * @param string $type
     * @return mixed|null
     */
    public function getEntityTypeIdByCode($code, $type = self::TYPE_SOURCE)
    {
        $entityTypeId = null;
        foreach ($this->initialData->getEntityTypes($type) as $entityType) {
            if ($entityType['entity_type_code'] == $code) {
                $entityTypeId = $entityType['entity_type_id'];
            }
        }
        return $entityTypeId;
    }

    /**
     * Get default product attribute groups
     *
     * @return array
     */
    public function getDefaultProductAttributeGroups()
    {
        $defaultProductAttributeSetId = $this->getProductAttributeSets(
            self::ATTRIBUTE_SETS_DEFAULT,
            self::TYPE_DEST
        )['attribute_set_id'];
        $attributeGroups = [];
        foreach ($this->initialData->getAttributeGroups(self::TYPE_DEST) as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $defaultProductAttributeSetId) {
                $attributeGroup['attribute_group_id'] = null;
                $attributeGroup['attribute_set_id'] = null;
                $attributeGroups[] = $attributeGroup;
            }
        }
        return $attributeGroups;
    }

    /**
     * Get default product entity attributes
     *
     * @return array
     */
    public function getDefaultProductEntityAttributes()
    {
        $defaultProductAttributeSetId = $this->getProductAttributeSets(
            self::ATTRIBUTE_SETS_DEFAULT,
            self::TYPE_DEST
        )['attribute_set_id'];
        $entityAttributes = [];
        foreach ($this->initialData->getEntityAttributes(self::TYPE_DEST) as $entityAttribute) {
            if ($entityAttribute['attribute_set_id'] == $defaultProductAttributeSetId) {
                $entityAttribute['entity_attribute_id'] = null;
                $entityAttribute['attribute_set_id'] = null;
                $entityAttributes[] = $entityAttribute;
            }
        }
        return $entityAttributes;
    }

    /**
     * Get attribute group id for attribute set
     *
     * @param int $prototypeAttributeGroupId
     * @param int $attributeSetId
     * @return mixed|null
     */
    public function getAttributeGroupIdForAttributeSet($prototypeAttributeGroupId, $attributeSetId)
    {
        $attributeGroupId = null;
        $attributeGroupCode = $this->getDestAttributeGroupCodeFromId($prototypeAttributeGroupId);
        foreach ($this->helper->getDestinationRecords('eav_attribute_group') as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $attributeSetId
                && $attributeGroup['attribute_group_code'] == $attributeGroupCode
            ) {
                $attributeGroupId = $attributeGroup['attribute_group_id'];
            }
        }
        return $attributeGroupId;
    }

    /**
     * Get destination attribute group code from id
     *
     * @param int $attributeGroupId
     * @return mixed|null
     */
    public function getDestAttributeGroupCodeFromId($attributeGroupId)
    {
        $attributeGroupCode = null;
        foreach ($this->initialData->getAttributeGroups(self::TYPE_DEST) as $attributeGroup) {
            if ($attributeGroup['attribute_group_id'] == $attributeGroupId) {
                $attributeGroupCode = $attributeGroup['attribute_group_code'];
            }
        }
        return $attributeGroupCode;
    }

    /**
     * Get source attribute group name from id
     *
     * @param int $attributeGroupId
     * @return mixed|null
     */
    public function getSourceAttributeGroupNameFromId($attributeGroupId)
    {
        $attributeGroupName = null;
        foreach ($this->initialData->getAttributeGroups(self::TYPE_SOURCE) as $attributeGroup) {
            if ($attributeGroup['attribute_group_id'] == $attributeGroupId) {
                $attributeGroupName = $attributeGroup['attribute_group_name'];
            }
        }
        return $attributeGroupName;
    }

    /**
     * Get custom product attribute groups
     *
     * @param int $attributeSetId
     * @return array
     */
    public function getCustomProductAttributeGroups($attributeSetId)
    {
        $defaultAttributeGroupNames = [];
        $sourceAttributeGroupNames = [];
        foreach ($this->getDefaultProductAttributeGroups() as $attributeGroup) {
            $defaultAttributeGroupNames[] = $attributeGroup['attribute_group_name'];
        }
        foreach ($this->helper->getSourceRecords('eav_attribute_group') as $attributeGroup) {
            if ($attributeGroup['attribute_set_id'] == $attributeSetId
                && !in_array($attributeGroup['attribute_group_name'], $this->excludedProductAttributeGroups)
            ) {
                $sourceAttributeGroupNames[$attributeGroup['attribute_group_id']]
                    = $attributeGroup['attribute_group_name'];
            }
        }
        return array_keys(array_diff($sourceAttributeGroupNames, $defaultAttributeGroupNames));
    }

    /**
     * Get custom attribute ids
     *
     * @return array
     */
    public function getCustomAttributeIds()
    {
        $defaultAttributes = [];
        $entityTypesSource = $this->initialData->getEntityTypes(self::TYPE_SOURCE);
        $entityTypesDest = $this->initialData->getEntityTypes(self::TYPE_DEST);
        foreach ($this->initialData->getAttributes(self::TYPE_DEST) as $id => $attribute) {
            $defaultAttributes[$id] =
                $entityTypesDest[$attribute['entity_type_id']]['entity_type_code']
                . '--'
                . $attribute['attribute_code'];
        }
        $sourceAttributes = $this->ignoredAttributes->clearIgnoredAttributes(
            $this->initialData->getAttributes(self::TYPE_SOURCE)
        );
        foreach ($sourceAttributes as $id => $attribute) {
            $sourceAttributes[$id] =
                $entityTypesSource[$attribute['entity_type_id']]['entity_type_code']
                . '--'
                . $attribute['attribute_code'];
        }
        return array_keys(array_diff($sourceAttributes, $defaultAttributes));
    }
}
