<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\SalesIncrement;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\ResourceModel;
use Migration\App\ProgressBar;

class Volume extends AbstractVolume
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
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progressBar;

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
     * @return bool
     */
    public function perform()
    {
        $this->progressBar->start(1);
        $this->progressBar->advance();
        /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
        $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
        foreach ($this->helper->getEntityTypeTablesMap() as $entityType) {
            foreach ($this->helper->getStoreIds() as $storeId) {
                $incrementMaxNumber = $this->helper->getMaxIncrementForEntityType($entityType['entity_type_id']);
                $select = $adapter->select()
                    ->from($this->helper->getTableName($entityType['entity_type_table'], $storeId))
                    ->order("{$entityType['column']} DESC")
                    ->limit(1);
                $lastInsertId = $adapter->fetchOne($select);
                if ($incrementMaxNumber != $lastInsertId) {
                    $this->errors[] = sprintf(
                        'Mismatch in last increment id of %s entity',
                        $entityType['entity_type_code']
                    );
                    continue 2;
                }
            }
        }
        $this->progressBar->finish();
        return $this->checkForErrors();
    }
}
