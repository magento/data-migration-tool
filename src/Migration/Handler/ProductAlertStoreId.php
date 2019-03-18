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
 * Handler for product alert store id
 */
class ProductAlertStoreId extends AbstractHandler
{
    /**
     * @var array
     */
    protected $storeWebsite = [];

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
        $websiteId = $recordToHandle->getValue('website_id');
        if (empty($websiteId)) {
            return;
        }
        $storeId = $this->getDefaultStoreId($websiteId);
        $recordToHandle->setValue($this->field, $storeId);
    }

    /**
     * Get default store id
     *
     * @param int|string $websiteId
     * @return array
     */
    private function getDefaultStoreId($websiteId)
    {
        if (empty($this->storeWebsite[$websiteId])) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from(
                ['cw' => $this->source->addDocumentPrefix('core_website')],
                []
            )->join(
                ['csg' => $this->source->addDocumentPrefix('core_store_group')],
                'csg.group_id = cw.default_group_id',
                ['default_store_id']
            )->where('cw.website_id = ?', $websiteId);
            $this->storeWebsite[$websiteId] = $query->getAdapter()->fetchOne($query);
        }
        return $this->storeWebsite[$websiteId];
    }
}
