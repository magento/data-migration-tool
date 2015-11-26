<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\App\Step\AbstractDelta;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;
use Migration\Reader\MapInterface;

class Delta extends AbstractDelta
{
    /**
     * @var string
     */
    protected $mapConfigOption = 'customer_map_file';

    /**
     * @var string
     */
    protected $groupName = 'delta_customer';

    /**
     * @var Helper
     */
    protected $helper;

    /**
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
     * {@inheritdoc}
     */
    protected function processChangedRecords($documentName, $idKey)
    {
        $items = $this->source->getChangedRecords($documentName, $idKey);
        if (empty($items)) {
            return;
        }
        if (!$this->eolOnce) {
            $this->eolOnce = true;
            echo PHP_EOL;
        }
        $destinationName = $this->mapReader->getDocumentMap($documentName, MapInterface::TYPE_SOURCE);

        $attributeType = $this->helper->getAttributeType($documentName);

        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);
        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
        do {
            $destinationRecords = $destDocument->getRecords();

            $ids = [];

            foreach ($items as $data) {
                echo('.');
                $ids[] = $data[$idKey];

                if ($this->helper->isSkipRecord($attributeType, $documentName, $data)) {
                    continue;
                }

                $this->transformData(
                    $data,
                    $sourceDocument,
                    $destDocument,
                    $recordTransformer,
                    $destinationRecords
                );
            }
            $this->helper->updateAttributeData($attributeType, $documentName, $destinationRecords);

            $this->destination->updateChangedRecords($destinationName, $destinationRecords);
            $documentNameDelta = $this->source->getDeltaLogName($documentName);
            $documentNameDelta = $this->source->addDocumentPrefix($documentNameDelta);
            $this->markRecordsProcessed($documentNameDelta, $idKey, $ids);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
    }
}
