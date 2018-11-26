<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Adapter\Mysql;

/**
 * Handler for finding bundle product id and set it to processed field
 */
class BundleParentProduct extends AbstractHandler
{
    /**
     * @var string
     */
    private $parentField;

    /**
     * @var string
     */
    private $documentWithProductId;

    /**
     * @var string
     */
    private $fieldWithProductId;

    /**
     * @var Source
     */
    private $source;

    /**
     * @param Source $source
     * @param string $parentField
     * @param string $documentWithProductId
     * @param string $fieldWithProductId
     */
    public function __construct(Source $source, $parentField, $documentWithProductId, $fieldWithProductId)
    {
        $this->source = $source;
        $this->parentField = $parentField;
        $this->documentWithProductId = $documentWithProductId;
        $this->fieldWithProductId = $fieldWithProductId;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $parentRowId  = $recordToHandle->getValue($this->parentField);
        $productId = $this->getProductId($parentRowId);
        $productId = $productId ?: 0;
        $recordToHandle->setValue($this->field, $productId);
    }

    /**
     * Find product
     *
     * @param int $parentRowId
     * @return mixed
     */
    private function getProductId($parentRowId)
    {
        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from(
            $this->source->addDocumentPrefix($this->documentWithProductId),
            [$this->fieldWithProductId]
        )->where("{$this->parentField} = ?", $parentRowId);
        return $query->getAdapter()->fetchOne($query);
    }
}
