<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param string $documentName
     * @param string $idKey
     * @return void
     */
    protected function processChangedRecords($documentName, $idKey)
    {
        $destinationName = $this->mapReader->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);

        $items = $this->source->getChangedRecords($documentName, $idKey);

        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);

        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);

        $eavDocumentName = $this->helper->getDestEavDocument();
        $eavDocumentResource = $this->destination->getDocument($eavDocumentName);

        do {
            $destinationRecords = $destDocument->getRecords();
            $destEavCollection = $eavDocumentResource->getRecords();

            $ids = [];

            foreach ($items as $data) {
                echo('.');
                $ids[] = $data[$idKey];

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

            $this->markRecordsProcessed($this->source->getDeltaLogName($documentName), $idKey, $ids);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
    }
}
