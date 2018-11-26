<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesOrder;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\Reader\MapInterface;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        Helper $helper,
        Logger $logger
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('sales_order_map_file');
        $this->progress = $progress;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Entry point. Run migration of SalesOrder structure.
     *
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $sourceDocuments = array_keys($this->helper->getDocumentList());
        foreach ($sourceDocuments as $sourceDocName) {
            $sourceDocument = $this->source->getDocument($sourceDocName);

            $destinationDocumentName = $this->map->getDocumentMap(
                $sourceDocName,
                MapInterface::TYPE_SOURCE
            );
            if (!$destinationDocumentName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationDocumentName);
            $this->destination->clearDocument($destinationDocumentName);

            $eavDocumentName = $this->helper->getDestEavDocument();
            $eavDocumentResource = $this->destination->getDocument($eavDocumentName);

            /** @var \Migration\RecordTransformer $recordTransformer */
            $recordTransformer = $this->recordTransformerFactory->create(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destDocument,
                    'mapReader' => $this->map
                ]
            );
            $recordTransformer->init();
            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocName), LogManager::LOG_LEVEL_DEBUG);
            while (!empty($bulk = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;
                $destinationCollection = $destDocument->getRecords();
                $destEavCollection = $eavDocumentResource->getRecords();
                foreach ($bulk as $recordData) {
                    $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                    /** @var Record $sourceRecord */
                    $sourceRecord = $this->recordFactory->create(
                        ['document' => $sourceDocument, 'data' => $recordData]
                    );
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($sourceRecord, $destRecord);
                    $destinationCollection->addRecord($destRecord);

                    $this->migrateAdditionalOrderData($recordData, $sourceDocument, $destEavCollection);
                }
                $this->destination->saveRecords($destinationDocumentName, $destinationCollection);
                $this->destination->saveRecords($eavDocumentName, $destEavCollection);
                $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
            }
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        $iterations = 0;
        foreach (array_keys($this->helper->getDocumentList()) as $document) {
            $iterations += $this->source->getRecordsCount($document);
        }
        return $iterations;
    }

    /**
     * Migrate additional order data
     *
     * @param array $data
     * @param ResourceModel\Document $sourceDocument
     * @param Record\Collection $destEavCollection
     * @return void
     */
    public function migrateAdditionalOrderData($data, $sourceDocument, $destEavCollection)
    {
        foreach ($this->helper->getEavAttributes() as $orderEavAttribute) {
            $eavAttributeData = $this->prepareEavEntityData($orderEavAttribute, $data);
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

    /**
     * Prepare eav entity data
     *
     * @param string $eavAttribute
     * @param array $recordData
     * @return array|null
     */
    protected function prepareEavEntityData($eavAttribute, $recordData)
    {
        $recordEavData = null;
        $value = $this->getAttributeValue($recordData, $eavAttribute);
        if ($value != null) {
            $attributeData = $this->getAttributeData($eavAttribute);
            $recordEavData = [
                'attribute_id' => $attributeData['attribute_id'],
                'entity_type_id' => $attributeData['entity_type_id'],
                'store_id' => $recordData['store_id'],
                'entity_id' => $recordData['entity_id'],
                'value' => $value
            ];
        }
        return $recordEavData;
    }

    /**
     * Get attribute data
     *
     * @param string $eavAttributeCode
     * @return array|null
     */
    protected function getAttributeData($eavAttributeCode)
    {
        $attributeData = null;
        $pageNumber = 0;
        while (!empty($bulk = $this->destination->getRecords('eav_attribute', $pageNumber))) {
            $pageNumber++;
            foreach ($bulk as $eavData) {
                if ($eavData['attribute_code'] == $eavAttributeCode) {
                    $attributeData = $eavData;
                    break;
                }
            }
        }
        return $attributeData;
    }

    /**
     * Get attribute value
     *
     * @param array $recordData
     * @param string $attributeName
     * @return array|null
     */
    protected function getAttributeValue($recordData, $attributeName)
    {
        $attributeValue = null;
        if (isset($recordData[$attributeName])) {
            return $attributeValue = $recordData[$attributeName];
        }
        return $attributeValue;
    }

    /**
     * Get dest eav document
     *
     * @return int
     */
    protected function getDestEavDocument()
    {
        return count($this->helper->getDocumentList());
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        throw new \Migration\Exception('Rollback is impossible');
    }
}
