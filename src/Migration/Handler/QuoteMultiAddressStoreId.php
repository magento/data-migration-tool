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
 * Handler for quote multi address store id
 */
class QuoteMultiAddressStoreId extends AbstractHandler
{
    /**
     * @var Source
     */
    protected $source;

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
        $this->validate($recordToHandle);
        $quoteItemId = $recordToHandle->getValue('quote_item_id');
        $storeId = $this->getStoreId($quoteItemId);
        $recordToHandle->setValue($this->field, $storeId);
    }

    /**
     * Get store id
     *
     * @param int|string $quoteItemId
     * @return string
     */
    private function getStoreId($quoteItemId)
    {
        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $query = $adapter->getSelect()->from(
            [$this->source->addDocumentPrefix('sales_flat_quote_item')],
            ['store_id']
        )->where('item_id = ?', $quoteItemId);
        return $query->getAdapter()->fetchOne($query);
    }
}
