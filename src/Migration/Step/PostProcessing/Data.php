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
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;


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
     * @var \Migration\Reader\Groups
     */
    protected $readerAttributes;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param GroupsFactory $groupsFactory
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        GroupsFactory $groupsFactory,
        Logger $logger,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progress = $progress;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->readerAttributes = $groupsFactory->create('eav_attribute_groups_file');
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $this->deleteMissingProductAttributeValues();
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
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
        foreach ($this->helper->getProductDestinationDocumentFields() as $document => $fields) {
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
            $this->destination->deleteRecords($document, 'attribute_id', $attributeIds);
        }
    }

    /**
     * @return array
     */
    protected function getMissingProductAttributeIds()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();

        $selects = [];
        foreach ($this->helper->getProductDestinationDocumentFields() as $document => $fields) {
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
