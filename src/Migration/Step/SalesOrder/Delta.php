<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\SalesOrder;

use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\MapReader\MapReaderSalesOrder;
use Migration\ProgressBar;

class Delta
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var \Migration\Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var MapReaderSalesOrder
     */
    protected $mapReader;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;


    /**
     * @param Source $source
     * @param Destination $destination,
     * @param MapReaderSalesOrder $mapReader
     * @param ProgressBar $progress
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Helper $helper
     * @param \Migration\Resource\RecordFactory $recordFactory
     */
    public function __construct(
        Source $source,
        Destination $destination,
        MapReaderSalesOrder $mapReader,
        ProgressBar $progress,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Helper $helper,
        \Migration\Resource\RecordFactory $recordFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->mapReader = $mapReader;
        $this->progress = $progress;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->helper = $helper;
        $this->recordFactory = $recordFactory;
    }

    /**
     * @return bool
     */
    public function delta()
    {
        $this->progress->start(count($this->helper->getDocumentList()));
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        foreach ($deltaDocuments as $sourceDocName => $idField) {
            $this->progress->advance();
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationDocumentName = $this->mapReader->getDocumentMap(
                $sourceDocName,
                MapReaderInterface::TYPE_SOURCE
            );
            if (!$destinationDocumentName) {
                continue;
            }
            $destDocumentResource = $this->destination->getDocument($destinationDocumentName);
            $eavDocumentName = $this->helper->getDestEavDocument();
            $eavDocumentResource = $this->destination->getDocument($eavDocumentName);

            $recordTransformer = $this->recordTransformerFactory->create(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destDocumentResource,
                    'mapReader' => $this->mapReader
                ]
            );
            $recordTransformer->init();
            while (!empty($sourceRecords = $this->source->getChangedRecords($sourceDocument, $idField))) {
                $destinationCollection = $destDocumentResource->getRecords();
                $destEavCollection = $eavDocumentResource->getRecords();
                foreach ($sourceRecords as $recordData) {
                    /** @var Record $sourceRecord */
                    $sourceRecord = $this->recordFactory->create(
                        ['document' => $sourceDocument, 'data' => $recordData]
                    );
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocumentResource]);
                    $recordTransformer->transform($sourceRecord, $destRecord);
                    $destinationCollection->addRecord($destRecord);
                    foreach ($this->helper->getEavAttributes() as $orderEavAttribute) {
                        $eavAttributeData = $this->prepareEavEntityData($orderEavAttribute, $recordData);
                        if ($eavAttributeData) {
                            $attributeRecord = $this->recordFactory->create(
                                [
                                    'document' => $sourceDocument,
                                    'data' => $eavAttributeData
                                ]
                            );
                            $destEavCollection->addRecord($attributeRecord);
                        }
                    }
                }
                $this->destination->updateChangedRecords($destinationDocumentName, $destinationCollection);
                $this->destination->updateChangedRecords($eavDocumentName, $destEavCollection);
            }
            // TODO: remove it when delta collecting tables will be cleared
            break;
        }
        return true;
    }
}
