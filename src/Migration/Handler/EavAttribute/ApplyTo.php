<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttribute;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;

/**
 * Class applyTo
 */
class ApplyTo extends AbstractHandler
{

    /**
     * @var string
     */
    private $eavAttributesData = [];

    /**
     * @var Source
     */
    private $source;

    /**
     * @var string
     */
    private $eavAttributeTable = 'eav_attribute';

    /**
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * Manually created product attributes should have NULL value
     *
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return void
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $fieldValueOpposite = $oppositeRecord->getValue($this->field);
        $attributeId = $recordToHandle->getValue('attribute_id');
        $attributeIdOpposite = $oppositeRecord->getValue('attribute_id');
        if (!$attributeIdOpposite && $this->checkIfUserDefined($attributeId)) {
            $recordToHandle->setValue($this->field, null);
        } else {
            $recordToHandle->setValue($this->field, $fieldValueOpposite);
        }
    }

    /**
     * Check if the attribute is user defined
     *
     * @param int $attributeId
     * @return bool
     */
    protected function checkIfUserDefined($attributeId)
    {
        if (empty($this->eavAttributesData)) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $select = $adapter->getSelect()
                ->from(
                    [$this->source->addDocumentPrefix($this->eavAttributeTable)],
                    ['attribute_id', 'is_user_defined']
                );
            $this->eavAttributesData = $select->getAdapter()->fetchAll($select);
        }
        foreach ($this->eavAttributesData as $attribute) {
            if ($attribute['attribute_id'] == $attributeId && $attribute['is_user_defined']) {
                return true;
            }
        }
        return false;
    }
}
