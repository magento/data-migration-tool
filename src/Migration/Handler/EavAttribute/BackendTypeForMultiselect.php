<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttribute;

use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;

/**
 * Class BackendTypeForMultiselect
 */
class BackendTypeForMultiselect extends AbstractHandler
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string
     */
    private $productEntityTypeId;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        if ($recordToHandle->getValue('frontend_input') == 'multiselect'
            && $recordToHandle->getValue('entity_type_id') == $this->getProductEntityTypeId()
        ) {
            $recordToHandle->setValue($this->field, 'varchar');
        }
    }


    /**
     * Get Product Entity Type Id
     *
     * @return int
     */
    protected function getProductEntityTypeId()
    {
        if (null === $this->productEntityTypeId) {
            /** @var \Magento\Framework\DB\Select $select */
            $select = $this->source->getAdapter()->getSelect();
            $select->from(
                ['eet' => $this->source->addDocumentPrefix('eav_entity_type')],
                ['eet.entity_type_id']
            )->where(
                'eet.entity_type_code = "catalog_product"'
            );
            $this->productEntityTypeId = $select->getAdapter()->fetchOne($select);
        }
        return $this->productEntityTypeId;
    }
}
