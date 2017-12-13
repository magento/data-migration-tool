<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Model\Eav;

/**
 * Class AttributeGroupNameMap
 */
class AttributeGroupNameToCodeMap
{
    /**
     * @var array
     */
    private $map = [
        'catalog_product' => [
            'General' => 'product-details',
            'Prices' => 'advanced-pricing',
            'Design' => 'design',
            'Images' => 'image-management'
        ]
    ];

    /**
     * @var string
     */
    protected $attributeGroupNamePrefix = 'Migration_';

    /**
     * @param string $groupName
     * @param string $entityType
     * @return array
     */
    public function getGroupCodeMap($groupName, $entityType)
    {
        $groupNameOriginal = preg_replace('/^' . $this->attributeGroupNamePrefix . '/', '', $groupName);
        $groupCodeMap = isset($this->map[$entityType][$groupNameOriginal])
            ? $this->map[$entityType][$groupNameOriginal]
            : null;
        $groupCodeTransformed = preg_replace('/[^a-z0-9]+/', '-', strtolower($groupName));
        $groupCode = $groupCodeMap ?: $groupCodeTransformed;
        return $groupCode;
    }

    /**
     * @param string $entityType
     * @return array
     */
    public function getMap($entityType)
    {
        return isset($this->map[$entityType]) ? $this->map[$entityType] : [];
    }
}
