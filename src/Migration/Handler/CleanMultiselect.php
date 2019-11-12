<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;

/**
 * Class CleanMultiselect transforms records with value 24,,,41,, into 24,41
 */
class CleanMultiselect extends AbstractHandler implements HandlerInterface
{
    /**
     * Attribute IDs
     *
     * @var array|bool
     */
    private $attributeIds = false;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var string
     */
    private $frontendInput = 'multiselect';

    /**
     * @var string
     */
    private $entityTypeCode = 'catalog_product';

    /**
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $attributeIds = $this->getAttributeIds();
        if ($attributeIds && in_array($recordToHandle->getValue('attribute_id'), $attributeIds)) {
            $value = $recordToHandle->getValue($this->field);
            $value = explode(',', $value);
            $value = array_filter($value);
            $value = implode(',', $value);
            $recordToHandle->setValue($this->field, $value);
        }
    }

    /**
     * Get attribute ids of multiselect frontend input
     *
     * @return array
     */
    private function getAttributeIds()
    {
        if ($this->attributeIds === false) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from(
                ['ea' => $this->source->addDocumentPrefix('eav_attribute')],
                ['ea.attribute_id']
            )->join(
                ['eet' => $this->source->addDocumentPrefix('eav_entity_type')],
                'ea.entity_type_id = eet.entity_type_id',
                []
            )->where(
                'ea.frontend_input = ?', $this->frontendInput
            )->where(
                'eet.entity_type_code = ?', $this->entityTypeCode
            );
            $this->attributeIds = $query->getAdapter()->fetchCol($query);
        }
        return $this->attributeIds;
    }
}
