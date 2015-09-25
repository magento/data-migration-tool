<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGridsEE;

use Migration\Handler;
use Migration\Resource;
use Migration\Resource\Record;
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
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param Config $config
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Logger $logger,
        Helper $helper,
        Config $config
    ) {
        parent::__construct($progress, $source, $destination, $recordFactory, $logger, $helper, $config);
    }

    /**
     * @param array $columns
     * @param \Zend_Db_Expr $entityIdsSelect
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesOrderGridArchive(array $columns, \Zend_Db_Expr $entityIdsSelect)
    {
        return parent::getSelectSalesOrderGrid($columns, $entityIdsSelect);
    }

    /**
     * @param array $columns
     * @param \Zend_Db_Expr $entityIdsSelect
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesInvoiceGridArchive(array $columns, \Zend_Db_Expr $entityIdsSelect)
    {
        return parent::getSelectSalesInvoiceGrid($columns, $entityIdsSelect);
    }

    /**
     * @param array $columns
     * @param \Zend_Db_Expr $entityIdsSelect
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesShipmentGridArchive(array $columns, \Zend_Db_Expr $entityIdsSelect)
    {
        return parent::getSelectSalesShipmentGrid($columns, $entityIdsSelect);
    }

    /**
     * @param array $columns
     * @param \Zend_Db_Expr $entityIdsSelect
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesCreditmemoGridArchive(array $columns, \Zend_Db_Expr $entityIdsSelect)
    {
        return parent::getSelectSalesCreditmemoGrid($columns, $entityIdsSelect);
    }
}
