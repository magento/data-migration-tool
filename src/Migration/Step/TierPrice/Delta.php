<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel;
use Migration\Config;

/**
 * Class Delta
 */
class Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'tier_price_map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_tier_price';

    /**
     * @var string
     */
    private $editionMigrate = '';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Data $data
     * @param Helper $helper
     * @param Config $config
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Config $config,
        Helper $helper
    ) {
        $this->editionMigrate = $config->getOption('edition_migrate');
        $this->helper = $helper;
        parent::__construct(
            $source,
            $mapFactory,
            $groupsFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory
        );
    }

    /**
     * @inheritdoc
     */
    protected function processDeletedRecords($documentName, $idKeys, $destinationName)
    {
        $idKeysDelete = $idKeys;
        $entityIdName = 'entity_id';
        $this->destination->getAdapter()->setForeignKeyChecks(1);
        while (!empty($items = $this->source->getDeletedRecords($documentName, $idKeys))) {
            $itemsDelete = $items;
            if ($this->editionMigrate != Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE) {
                $entityIdName = 'row_id';
                foreach ($itemsDelete as &$item) {
                    $item[$entityIdName] = $item['entity_id'];
                    unset($item['entity_id']);
                }
                $idKeysDelete = array_diff($idKeysDelete, ['entity_id']);
                $idKeysDelete[] = $entityIdName;
            }
            if ($documentName == 'catalog_product_entity_group_price') {
                foreach ($itemsDelete as &$item) {
                    $item['qty'] = 1;
                }
                $idKeysDelete[] = 'qty';
            }
            $this->destination->deleteRecords(
                $this->destination->addDocumentPrefix($destinationName),
                $idKeysDelete,
                $itemsDelete
            );
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
        }
        $this->destination->getAdapter()->setForeignKeyChecks(0);
    }

    /**
     * @inheritdoc
     */
    protected function getDocumentMap($document, $type)
    {
        return $this->helper->getMappedDocumentName($document, $type);
    }
}
