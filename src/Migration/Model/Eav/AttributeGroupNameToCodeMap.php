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
    protected $map = [
        'General' => 'product-details',
        'Prices' => 'advanced-pricing',
        'Design' => 'design',
        'Images' => 'image-management'
    ];

    /**
     * @var string
     */
    protected $attributeGroupNamePrefix = 'Migration_';

    /**
     * @param string $groupName
     * @return array
     */
    public function getGroupCodeMap($groupName)
    {
        $groupNameOriginal = preg_replace('/^' . $this->attributeGroupNamePrefix . '/', '', $groupName);
        $groupCodeMap = isset($this->map[$groupNameOriginal]) ? $this->map[$groupNameOriginal] : null;
        $groupCodeTransformed = preg_replace('/[^a-z0-9]+/', '-', strtolower($groupName));
        $groupCode = $groupCodeMap ?: $groupCodeTransformed;
        return $groupCode;
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }
}
