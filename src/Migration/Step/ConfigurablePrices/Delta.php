<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;

/**
 * Class Delta
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Delta extends AbstractDelta
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var string
     */
    protected $mapConfigOption = 'map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_configurable_price';

    /**
     * @var string
     */
    private $sourceDocumentName = 'catalog_product_super_attribute_pricing';

    /**
     * @var string
     */
    private $destinationDocumentName = 'catalog_product_entity_decimal';

    /**
     * Delta constructor.
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Helper $helper
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Helper $helper
    ) {
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
     * Perform
     *
     * @return bool
     * @throws \Migration\Exception
     */
    public function perform()
    {
        $sourceDocuments = array_flip($this->source->getDocumentList());
        foreach ($this->deltaDocuments as $documentName => $idKeys) {
            $idKeys = explode(',', $idKeys);
            if ($documentName != $this->sourceDocumentName || !$this->source->getDocument($documentName)) {
                continue;
            }
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            if (!isset($sourceDocuments[$documentNameDelta])) {
                throw new \Migration\Exception(sprintf('Deltalog for %s is not installed', $documentName));
            }
            if ($this->source->getRecordsCount($documentNameDelta) == 0) {
                continue;
            }

            $this->logger->debug(sprintf('%s has changes', $documentName));
            while (!empty($items = $this->source->getDeletedRecords($documentName, $idKeys))) {
                $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
            }
            $items = $this->source->getChangedRecords($documentName, $idKeys);
            if (empty($items)) {
                return true;
            }
            echo PHP_EOL;
            do {
                $changedEntityIds = $this->getProductIds(array_column($items, 'product_super_attribute_id'));
                /** @var \Magento\Framework\DB\Select $select */
                $select = $this->helper->getConfigurablePrice($changedEntityIds);
                $data = $this->source->getAdapter()->loadDataFromSelect($select);
                $this->destination->saveRecords(
                    $this->source->addDocumentPrefix($this->destinationDocumentName),
                    $data,
                    true
                );
                $this->markRecordsProcessed($documentNameDelta, $idKeys, $items);
            } while (!empty($items = $this->source->getChangedRecords($documentName, $idKeys)));
        }
        return true;
    }

    /**
     * Get product ids
     *
     * @param $productSuperAttributeIds
     * @return array
     */
    private function getProductIds($productSuperAttributeIds)
    {
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->source->getAdapter()->getSelect();
        $select->from(
            ['cpsa' => $this->source->addDocumentPrefix('catalog_product_super_attribute')],
            ['product_id']
        )->where('cpsa.product_super_attribute_id IN (?)', $productSuperAttributeIds);
        return array_unique($select->getAdapter()->fetchCol($select));
    }
}
