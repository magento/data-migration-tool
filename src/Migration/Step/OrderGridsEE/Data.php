<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Handler;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;
use Migration\Config;

/**
 * Class Data
 */
class Data extends \Migration\Step\OrderGrids\Data
{
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
        parent::__construct($progress, $source, $destination, $recordFactory, $logger, $helper, $config);
    }

    /**
     * Get select sales order grid archive
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesOrderGridArchive(array $columns, $entityIds)
    {
        return parent::getSelectSalesOrderGrid($columns, $entityIds);
    }

    /**
     * Get select sales invoice grid archive
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesInvoiceGridArchive(array $columns, $entityIds)
    {
        return parent::getSelectSalesInvoiceGrid($columns, $entityIds);
    }

    /**
     * Get select sales shipment grid archive
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesShipmentGridArchive(array $columns, $entityIds)
    {
        return parent::getSelectSalesShipmentGrid($columns, $entityIds);
    }

    /**
     * Get select sales creditmemo grid archive
     *
     * @param array $columns
     * @param \Zend_Db_Expr|array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesCreditmemoGridArchive(array $columns, $entityIds)
    {
        return parent::getSelectSalesCreditmemoGrid($columns, $entityIds);
    }
}
