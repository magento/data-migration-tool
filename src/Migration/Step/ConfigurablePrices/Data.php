<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Resource;
use Migration\Resource\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\Resource\Adapter\Mysql;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Mysql
     */
    protected $sourceAdapter;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param Logger $logger
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Logger $logger,
        \Migration\Step\ConfigurablePrices\Helper $helper
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->destination = $destination;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->helper->init();
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $document = $this->helper->getDocumentList();
        $sourceDocumentName = $document['source'];
        $destinationDocumentName = $document['destination'];
        $destinationDocument = $this->destination->getDocument($destinationDocumentName);
        $pageNumber = 0;
        $this->logger->debug('migrating', ['table' => $sourceDocumentName]);
        $this->progress->start($this->source->getRecordsCount($sourceDocumentName), LogManager::LOG_LEVEL_DEBUG);
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->getConfigurablePrice($sourceDocumentName);
        while (!empty($bulk = $this->getRecords($sourceDocumentName, $select, $pageNumber))) {
            $pageNumber++;
            $destinationCollection = $destinationDocument->getRecords();
            foreach ($bulk as $recordData) {
                $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                /** @var Record $destinationRecord */
                $destinationRecord = $this->recordFactory->create(
                    ['document' => $destinationDocument, 'data' => $recordData]
                );
                $destinationCollection->addRecord($destinationRecord);
            }
            $this->destination->saveRecords($destinationDocumentName, $destinationCollection);
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * @return int
     */
    protected function getIterationsCount()
    {
        $iterations = 0;
        $document = $this->helper->getDocumentList();
        $iterations += $this->source->getRecordsCount($document['source']);
        return $iterations;
    }

    /**
     * @param string $sourceDocumentName
     * @param \Magento\Framework\DB\Select $select
     * @param int $pageNumber
     * @return array
     */
    protected function getRecords($sourceDocumentName, \Magento\Framework\DB\Select $select, $pageNumber)
    {
        $select->limit(
            $this->source->getPageSize($sourceDocumentName),
            $pageNumber * $this->source->getPageSize($sourceDocumentName)
        );
        return $this->sourceAdapter->loadDataFromSelect($select);
    }

    /**
     * @param string $sourceDocument
     * @return \Magento\Framework\DB\Select
     */
    protected function getConfigurablePrice($sourceDocument)
    {
        $sourceDocumentName = $this->source->addDocumentPrefix($sourceDocument);
        $fields = [
            'store_id' => 'website_id',
            'value' => 'pricing_value',
            'entity_id' => 'l.product_id',
            'attribute_id' => 'cpsa.attribute_id'
        ];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();

        $select->from($sourceDocumentName, $fields)
            ->joinInner(
                ['cpsa' => $this->source->addDocumentPrefix('catalog_product_super_attribute')],
                'cpsa.product_super_attribute_id = ' . $sourceDocumentName . '.product_super_attribute_id',
                []
            )
            ->joinInner(
                ['l' => $this->source->addDocumentPrefix('catalog_product_super_link')],
                'cpsa.product_id = l.parent_id',
                []
            )
            ->joinInner(
                ['a' => $this->source->addDocumentPrefix('catalog_product_super_attribute')],
                'l.parent_id = a.product_id',
                []
            )
            ->joinInner(
                ['cp' => $this->source->addDocumentPrefix('catalog_product_entity_int')],
                'l.product_id = cp.entity_id AND cp.attribute_id = a.attribute_id AND cp.store_id = '
                . $sourceDocumentName . '.website_id',
                []
            )
            ->joinInner(
                ['apd' => $this->source->addDocumentPrefix('catalog_product_super_attribute_pricing')],
                'a.product_super_attribute_id = apd.product_super_attribute_id AND apd.pricing_value = '
                . $sourceDocumentName .'.pricing_value AND cp.value = apd.value_index',
                []
            )
            ->joinInner(
                ['le' => $this->source->addDocumentPrefix('catalog_product_entity')],
                'le.entity_id = l.product_id',
                []
            );
        return $select;
    }
}
