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
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Logger $logger,
        Helper $helper
    ) {
        parent::__construct($progress, $source, $destination, $recordFactory, $logger, $helper);
    }

    /**
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesOrderGridArchive(array $columns)
    {
        return parent::getSelectSalesOrderGrid($columns);
    }

    /**
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesInvoiceGridArchive(array $columns)
    {
        return parent::getSelectSalesInvoiceGrid($columns);
    }

    /**
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesShipmentGridArchive(array $columns)
    {
        return parent::getSelectSalesShipmentGrid($columns);
    }

    /**
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectSalesCreditmemoGridArchive(array $columns)
    {
        return parent::getSelectSalesCreditmemoGrid($columns);
    }
}
