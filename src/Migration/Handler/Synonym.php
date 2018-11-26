<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;

/**
 * Handler for synonyms
 */
class Synonym extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $synonymsTable = 'search_synonyms';

    /**
     * @var array
     */
    protected $storeWebsite = [];

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @param Source $source
     * @param Destination $destination
     */
    public function __construct(Source $source, Destination $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $synonyms = $recordToHandle->getValue($this->field);
        $storeId = $recordToHandle->getValue('store_id');
        if (empty($synonyms)) {
            return;
        }
        $synonyms .= sprintf('"%s",', $recordToHandle->getValue('query_text'));
        $websiteId = $this->getWebsiteId($storeId);
        $record = ['synonyms' => $synonyms, 'store_id' => $storeId, 'website_id' => $websiteId];
        $this->destination->saveRecords($this->synonymsTable, [$record]);
    }

    /**
     * Get website id
     *
     * @param int|string $storeId
     * @return array
     */
    protected function getWebsiteId($storeId)
    {
        if (empty($this->storeWebsite[$storeId])) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from($this->source->addDocumentPrefix('core_store'), ['website_id'])
                ->where('store_id = ?', $storeId);
            $this->storeWebsite[$storeId] = $query->getAdapter()->fetchOne($query);
        }
        return $this->storeWebsite[$storeId];
    }
}
