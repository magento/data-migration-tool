<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;

/**
 * Handler to set value for a field of a record for certain attribute code
 */
class SetValueAttributeCondition extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $attributeCode;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string
     */
    protected $eavAttributeTable = 'eav_attribute';

    /**
     * @var string
     */
    static protected $eavAttributeData = [];

    /**
     * @param string $attributeCode
     * @param string $value
     * @param Source $source
     */
    public function __construct($attributeCode, $value, Source $source)
    {
        $this->value = $value;
        $this->attributeCode = $attributeCode;
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        if ($this->checkAttributeIdCode($recordToHandle->getValue('attribute_id'), $this->attributeCode)) {
            if ('null' === $this->value) {
                $this->value = null;
            }
            $recordToHandle->setValue($this->field, $this->value);
        }
    }

    /**
     * Check if attribute id of record and attribute code are in main eav table
     *
     * @param int $attributeIdOfRecord
     * @param string $attributeCode
     * @return bool
     */
    protected function checkAttributeIdCode($attributeIdOfRecord, $attributeCode)
    {
        if (empty(self::$eavAttributeData)) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $select = $adapter->getSelect()
                ->from(
                    [$this->source->addDocumentPrefix($this->eavAttributeTable)],
                    ['attribute_id', 'attribute_code']
                );
            self::$eavAttributeData = $select->getAdapter()->fetchAll($select);
        }
        foreach (self::$eavAttributeData as $attribute) {
            if ($attribute['attribute_id'] == $attributeIdOfRecord && $attribute['attribute_code'] == $attributeCode) {
                return true;
            }
        }
        return false;
    }
}
