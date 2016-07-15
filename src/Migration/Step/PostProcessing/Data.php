<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\App\Progress;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

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
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progressBar;

    /**
     * @var Progress
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $deletedDocumentRowsCount;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     * @param Logger $logger
     * @param Progress $progress
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Logger $logger,
        Helper $helper,
        Progress $progress
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progressBar->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $this->deleteMissingProductAttributeValues();
        $this->progressBar->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Deletes product attributes in eav value tables for non existing attributes
     *
     * @return void
     */
    protected function deleteMissingProductAttributeValues()
    {
        $attributeIds = $this->getMissingProductAttributeIds();
        if (!$attributeIds) {
            return ;
        }
        foreach (array_keys($this->helper->getProductDestinationDocumentFields()) as $document) {
            $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);

            $rowsCountBefore = $this->destination->getRecordsCount($document);
            $this->destination->deleteRecords($document, 'attribute_id', $attributeIds);
            $deletedCount = $rowsCountBefore - $this->destination->getRecordsCount($document);
            if (!empty($deletedCount)) {
                $this->deletedDocumentRowsCount[$document] = $deletedCount;
            }
        }
        $this->progress->saveProcessedEntities(
            'PostProcessing',
            'deletedDocumentRowsCount',
            $this->deletedDocumentRowsCount
        );
    }

    /**
     * @return array
     */
    protected function getMissingProductAttributeIds()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();

        $selects = [];
        foreach (array_keys($this->helper->getProductDestinationDocumentFields()) as $document) {
            $selects[] = $adapter->getSelect()->from(
                ['ea' => $this->helper->getEavAttributeDocument()],
                []
            )->joinRight(
                ['j' => $document],
                'j.attribute_id = ea.attribute_id',
                ['attribute_id']
            )->where(
                'ea.attribute_id IS NULL'
            )->group(
                'j.attribute_id'
            );
        }
        $query = $adapter->getSelect()->union($selects, \Zend_Db_Select::SQL_UNION);
        return $query->getAdapter()->fetchCol($query);
    }

    /**
     * @return int
     */
    protected function getIterationsCount()
    {
        return count(array_keys($this->helper->getProductDestinationDocumentFields()));
    }
}
