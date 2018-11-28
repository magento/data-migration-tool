<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Model\Eav;

/**
 * Class AttributeGroupNameMap
 */
class AttributeGroupNameToCodeMap
{
    /**
     * @var \Magento\Framework\Filter\Translit
     */
    private $translitFilter;

    /**
     * @param \Magento\Framework\Filter\Translit $translitFilter
     */
    public function __construct(
        \Magento\Framework\Filter\Translit $translitFilter
    ) {
        $this->translitFilter = $translitFilter;
    }

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
     * Get group codemap
     *
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
        $groupCodeTransformed = trim(
            preg_replace(
                '/[^a-z0-9]+/',
                '-',
                $this->translitFilter->filter(strtolower($groupName))
            ),
            '-'
        );
        $groupCodeTransformed = empty($groupCodeTransformed) ? md5($groupCodeTransformed) : $groupCodeTransformed;
        $groupCode = $groupCodeMap ?: $groupCodeTransformed;
        return $groupCode;
    }

    /**
     * Get map
     *
     * @param string $entityType
     * @return array
     */
    public function getMap($entityType)
    {
        return isset($this->map[$entityType]) ? $this->map[$entityType] : [];
    }
}
