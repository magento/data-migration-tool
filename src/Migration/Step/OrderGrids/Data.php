<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

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
        Helper $helper
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
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        foreach ($this->getDocumentList() as $methodToExecute => $document) {
            $sourceDocumentName = $document['source'];
            $destinationDocumentName = $document['destination'];
            $columns = $document['columns'];
            $destinationDocument = $this->destination->getDocument($destinationDocumentName);
            $this->destination->clearDocument($destinationDocumentName);
            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocumentName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocumentName), LogManager::LOG_LEVEL_DEBUG);
            /** @var \Magento\Framework\DB\Select $select */
            $select = call_user_func_array([$this, $methodToExecute], [$sourceDocumentName, $columns]);
            while (!empty($bulk = $this->getRecords($select, $pageNumber))) {
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
        foreach ($this->getDocumentList() as $document) {
            $iterations += $this->source->getRecordsCount($document['source']);
        }
        return $iterations;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $pageNumber
     * @return array
     */
    protected function getRecords(\Magento\Framework\DB\Select $select, $pageNumber)
    {
        $select->limit($this->source->getPageSize(), $pageNumber * $this->source->getPageSize());
        return $this->sourceAdapter->loadDataFromSelect($select);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesOrderGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_order' => 'sales_flat_order'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_order.entity_id', [])
            ->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_order.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_order.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesInvoiceGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_invoice' => 'sales_flat_invoice'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_invoice.entity_id', [])
            ->joinLeft(
                ['sales_order' => 'sales_flat_order'],
                'sales_invoice.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_invoice.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_invoice.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesShipmentGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_shipment' => 'sales_flat_shipment'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_shipment.entity_id', [])
            ->joinLeft(
                ['sales_order' => 'sales_flat_order'],
                'sales_shipment.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_shipment.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_shipment.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesCreditmemoGrid($sourceGridDocument, array $columns)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['sales_creditmemo' => 'sales_flat_creditmemo'], [])
            ->joinInner($sourceGridDocument, $sourceGridDocument . '.entity_id = sales_creditmemo.entity_id', [])
            ->joinLeft(
                ['sales_order' => 'sales_flat_order'],
                'sales_creditmemo.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => 'sales_flat_order_address'],
                'sales_creditmemo.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => 'sales_flat_order_address'],
                'sales_creditmemo.billing_address_id = sales_billing_address.entity_id',
                []
            );
        $select->columns($columns);
        return $select;
    }

    /**
     * @return array
     */
    protected function getDocumentList()
    {
        return $this->helper->getSelectData();
    }
}
