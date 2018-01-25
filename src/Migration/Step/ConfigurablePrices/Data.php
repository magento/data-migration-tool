<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\Config;

/**
 * Class Data
 */
class Data implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var Mysql
     */
    protected $sourceAdapter;

    /**
     * @var ResourceModel\Destination
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
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param Config $config
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        Logger $logger,
        \Migration\Step\ConfigurablePrices\Helper $helper,
        Config $config
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
        $this->destination = $destination;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->editionMigrate = $config->getOption('edition_migrate');
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
        $select = $this->getConfigurablePrice();
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
            $this->destination->saveRecords($destinationDocumentName, $destinationCollection, true);
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
     * @return \Magento\Framework\DB\Select
     */
    protected function getConfigurablePrice()
    {
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            ? 'entity_id'
            : 'row_id';
        $priceAttributeId = $this->getPriceAttributeId();
        $entitiesExpr = new \Zend_Db_Expr(
            'select product_id from ' . $this->source->addDocumentPrefix('catalog_product_super_attribute')
        );
        $priceExpr = new \Zend_Db_Expr(
            'IF(sup_ap.is_percent = 1, TRUNCATE(mt.value + (mt.value * sup_ap.pricing_value/100), 4), ' .
            ' mt.value + sup_ap.pricing_value)'
        );
        $fields = [
            'value' => $priceExpr,
            'attribute_id' => new \Zend_Db_Expr($priceAttributeId)
        ];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['mt' => $this->source->addDocumentPrefix('catalog_product_entity_decimal')], $fields)
            ->joinLeft(
                ['sup_a' => $this->source->addDocumentPrefix('catalog_product_super_attribute')],
                'mt.entity_id = product_id',
                []
            )
            ->joinInner(
                ['sup_ap' => $this->source->addDocumentPrefix('catalog_product_super_attribute_pricing')],
                'sup_ap.product_super_attribute_id = sup_a.product_super_attribute_id',
                ['store_id' => 'website_id']
            )
            ->joinInner(
                ['supl' => $this->source->addDocumentPrefix('catalog_product_super_link')],
                'mt.entity_id = supl.parent_id',
                [$entityIdName => 'product_id']
            )
            ->joinInner(
                ['pint' => $this->source->addDocumentPrefix('catalog_product_entity_int')],
                'pint.entity_id = supl.product_id and pint.attribute_id = sup_a.attribute_id ' .
                ' and pint.value = sup_ap.value_index',
                []
            )
            ->joinInner(
                ['cs' => $this->source->addDocumentPrefix('core_store')],
                'cs.website_id = sup_ap.website_id',
                ['store_id']
            )
            ->where('mt.entity_id in (?)', $entitiesExpr)
            ->where('mt.attribute_id = ?', $priceAttributeId)
        ;
        return $select;
    }

    /**
     * @return string
     */
    protected function getPriceAttributeId()
    {
        $select = $this->sourceAdapter->getSelect();
        $select->from($this->source->addDocumentPrefix('eav_attribute'))->where('attribute_code = ?', 'price');
        return $select->getAdapter()->fetchOne($select);
    }
}
