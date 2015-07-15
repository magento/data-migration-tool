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
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesOrderGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesOrderGrid($sourceGridDocument, $columns);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesInvoiceGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesInvoiceGrid($sourceGridDocument, $columns);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesShipmentGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesShipmentGrid($sourceGridDocument, $columns);
    }

    /**
     * @param string $sourceGridDocument
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectSalesCreditmemoGridArchive($sourceGridDocument, array $columns)
    {
        return parent::getSelectSalesCreditmemoGrid($sourceGridDocument, $columns);
    }
}
