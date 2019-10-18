<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\ResourceModel;
use Migration\App\ProgressBar;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     * @param ProgressBar\LogLevelProcessor $progressBar
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Helper $helper,
        ProgressBar\LogLevelProcessor $progressBar
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
        $this->progressBar = $progressBar;
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progressBar->start(1);
        $this->progressBar->advance();
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        foreach ($this->helper->getEntityTypeTablesMap() as $entityType) {
            foreach ($this->helper->getStoreIds() as $storeId) {
                $incrementNumber = $this->helper->getIncrementForEntityType(
                    $entityType['entity_type_id'],
                    $storeId
                );
                $select = $adapter->select()
                    ->from($this->helper->getTableName($entityType['entity_type_table'], $storeId))
                    ->order("{$entityType['column']} DESC")
                    ->limit(1);
                $lastInsertId = $adapter->fetchOne($select);
                if ($incrementNumber != $lastInsertId) {
                    $this->errors[] = sprintf(
                        'Mismatch in last increment id of %s entity: Source: %s Destination: %s',
                        $entityType['entity_type_code'],
                        $incrementNumber,
                        $lastInsertId
                    );
                    continue 2;
                }
            }
        }
        $this->progressBar->finish();
        return $this->checkForErrors();
    }
}
