<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\SalesOrder;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapInterface;
use Migration\ResourceModel\Source;
use Migration\ResourceModel\Destination;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;

/**
 * Class Delta
 */
class Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'sales_order_map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_sales';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @param Source $source
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Helper $helper
     * @param Data $data
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Helper $helper,
        Data $data
    ) {
        $this->helper = $helper;
        $this->data = $data;
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
    protected function processChangedRecords($documentName, $idKeys)
    {
        $destinationName = $this->mapReader->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);
        $items = $this->source->getChangedRecords($documentName, $idKeys);
        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);
        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
        $eavDocumentName = $this->helper->getDestEavDocument();
        $eavDocumentResource = $this->destination->getDocument($eavDocumentName);
        do {
            $destinationRecords = $destDocument->getRecords();
            $destEavCollection = $eavDocumentResource->getRecords();
            foreach ($items as $data) {
                echo('.');
                $this->transformData(
                    $data,
                    $sourceDocument,
                    $destDocument,
                    $recordTransformer,
                    $destinationRecords
                );
                $this->data->migrateAdditionalOrderData($data, $sourceDocument, $destEavCollection);
            }
            $this->destination->updateChangedRecords($destinationName, $destinationRecords);
            $this->destination->updateChangedRecords($eavDocumentName, $destEavCollection);
            $this->markRecordsProcessed($this->source->getDeltaLogName($documentName), $idKeys, $items);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKeys)));
    }
}
