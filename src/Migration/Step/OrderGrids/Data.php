<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\App\Step\StageInterface;
use Migration\Config;
use Migration\Handler;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\ResourceModel\Adapter\Mysql;

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
     * @var Mysql
     */
    protected $destinationAdapter;

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
     * @var Config
     */
    protected $config;

    /**
     * @var bool
     */
    protected $copyDirectly;

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
        Helper $helper,
        Config $config
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->destinationAdapter = $this->destination->getAdapter();
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->config = $config;
        $this->copyDirectly = (bool)$this->config->getOption('direct_document_copy');
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        foreach ($this->getDocumentList() as $methodToExecute => $document) {
            $destinationDocumentName = $document['destination'];
            $this->destination->clearDocument($destinationDocumentName);
            $this->progress->start(1, LogManager::LOG_LEVEL_DEBUG);

            $sourceGridDocument = array_flip($this->helper->getDocumentList())[$destinationDocumentName];
            $isCopiedDirectly = $this->isCopiedDirectly(
                $methodToExecute,
                $document['columns'],
                $destinationDocumentName,
                $sourceGridDocument
            );
            if (!$isCopiedDirectly) {
                $pageNumber = 0;
                while (!empty($entityIds = $this->getEntityIds($sourceGridDocument, $pageNumber))) {
                    $pageNumber++;
                    $this->destination->getAdapter()->insertFromSelect(
                        $this->{$methodToExecute}($document['columns'], $entityIds),
                        $this->destination->addDocumentPrefix($destinationDocumentName),
                        [],
                        \Magento\Framework\Db\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                    );
                }
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Performance optimized way. In case when source has direct access to destination database
     *
     * @param string $methodToExecute
     * @param array $columns
     * @param string $destinationDocumentName
     * @param string $sourceGridDocument
     * @return bool|void
     */
    protected function isCopiedDirectly(
        $methodToExecute,
        array $columns,
        $destinationDocumentName,
        $sourceGridDocument
    ) {
        if (!$this->copyDirectly) {
            return;
        }
        $result = true;
        try {
            $entityIdsSelect = $this->getEntityIdsSelect($sourceGridDocument);
            $this->destination->getAdapter()->insertFromSelect(
                $this->{$methodToExecute}($columns, new \Zend_Db_Expr($entityIdsSelect)),
                $this->destination->addDocumentPrefix($destinationDocumentName),
                [],
                \Magento\Framework\Db\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
        } catch (\Exception $e) {
            $this->copyDirectly = false;
            $this->logger->error(
                'Document ' . $sourceGridDocument . ' can not be copied directly because of error: '
                . $e->getMessage()
            );
            $result = false;
        }

        return $result;
    }

    /**
     * Get iterations count
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->getDocumentList());
    }

    /**
     * Get select sales order grid
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesOrderGrid(array $columns, $entityIds)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destinationAdapter->getSelect();
        $select->from(['sales_order' => $this->destination->addDocumentPrefix('sales_order')], [])
            ->joinLeft(
                ['sales_shipping_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_order.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_order.billing_address_id = sales_billing_address.entity_id',
                []
            )->where('sales_order.entity_id in (?)', $entityIds);
        $select->columns($columns);
        return $select;
    }

    /**
     * Get select sales invoice grid
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesInvoiceGrid(array $columns, $entityIds)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destinationAdapter->getSelect();
        $select->from(['sales_invoice' => $this->destination->addDocumentPrefix('sales_invoice')], [])
            ->joinLeft(
                ['sales_order' => $this->destination->addDocumentPrefix('sales_order')],
                'sales_invoice.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_invoice.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_invoice.billing_address_id = sales_billing_address.entity_id',
                []
            )->where('sales_invoice.entity_id in (?)', $entityIds);
        $select->columns($columns);
        return $select;
    }

    /**
     * Get select sales shipment grid
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesShipmentGrid(array $columns, $entityIds)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destinationAdapter->getSelect();
        $select->from(['sales_shipment' => $this->destination->addDocumentPrefix('sales_shipment')], [])
            ->joinLeft(
                ['sales_order' => $this->destination->addDocumentPrefix('sales_order')],
                'sales_shipment.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_shipment.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_shipment.billing_address_id = sales_billing_address.entity_id',
                []
            )->where('sales_shipment.entity_id in (?)', $entityIds);
        $select->columns($columns);
        return $select;
    }

    /**
     * Get select sales creditmemo grid
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesCreditmemoGrid(array $columns, $entityIds)
    {
        foreach ($columns as $key => $value) {
            $columns[$key] = new \Zend_Db_Expr($value);
        }
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->destinationAdapter->getSelect();
        $select->from(['sales_creditmemo' => $this->destination->addDocumentPrefix('sales_creditmemo')], [])
            ->joinLeft(
                ['sales_order' => $this->destination->addDocumentPrefix('sales_order')],
                'sales_creditmemo.order_id = sales_order.entity_id',
                []
            )->joinLeft(
                ['sales_shipping_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_creditmemo.shipping_address_id = sales_shipping_address.entity_id',
                []
            )->joinLeft(
                ['sales_billing_address' => $this->destination->addDocumentPrefix('sales_order_address')],
                'sales_creditmemo.billing_address_id = sales_billing_address.entity_id',
                []
            )->where('sales_creditmemo.entity_id in (?)', $entityIds);
        $select->columns($columns);
        return $select;
    }

    /**
     * Get document list
     *
     * @return array
     */
    protected function getDocumentList()
    {
        return $this->helper->getSelectData();
    }

    /**
     * Get entity ids
     *
     * @param string $sourceGridDocumentName
     * @param int $pageNumber
     * @return array
     */
    protected function getEntityIds($sourceGridDocumentName, $pageNumber)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect();
        $select->from($this->source->addDocumentPrefix($sourceGridDocumentName), 'entity_id')
            ->limit(
                $this->source->getPageSize($sourceGridDocumentName),
                $pageNumber * $this->source->getPageSize($sourceGridDocumentName)
            );
        $ids = $select->getAdapter()->fetchCol($select);
        return $ids;
    }

    /**
     * Get entity ids select
     *
     * @param string $sourceGridDocumentName
     * @return \Magento\Framework\DB\Select
     */
    protected function getEntityIdsSelect($sourceGridDocumentName)
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->getSelect();
        $schema = $this->config->getSource()['database']['name'];
        $select->from($this->source->addDocumentPrefix($sourceGridDocumentName), 'entity_id', $schema);
        return $select;
    }
}
