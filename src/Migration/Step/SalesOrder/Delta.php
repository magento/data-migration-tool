<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\SalesOrder;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\Reader\MapFactory;
use Migration\Resource;

class Delta extends AbstractDelta
{
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
     * @param Logger $logger
     * @param Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Helper $helper
     * @param Data $data
     * @param string $mapConfigOption
     */
    public function __construct(
        Source $source,
        MapFactory $mapFactory,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Helper $helper,
        Data $data,
        $mapConfigOption = 'sales_order_map_file'
    ) {
        $this->helper = $helper;
        $this->data = $data;
        parent::__construct(
            $source,
            $mapFactory,
            $logger,
            $destination,
            $recordFactory,
            $recordTransformerFactory,
            $mapConfigOption
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

            $this->source->deleteRecords($this->source->getDeltaLogName($documentName), $idKey, $ids);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
    }
}
