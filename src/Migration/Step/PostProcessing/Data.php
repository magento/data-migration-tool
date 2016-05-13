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
        $this->deleteIgnoredProductAttributeValues();
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Delete leftovers of ignored product attributes in eav value tables
     *
     * @return void
     */
    protected function deleteIgnoredProductAttributeValues()
    {
        $attributeIds = $this->getIgnoredProductAttributeIds();
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
    protected function getIgnoredProductAttributeIds()
    {
        $attributeCodes = array_keys($this->readerAttributes->getGroup('ignore'));
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect()->from(
            ['ea' => $this->helper->getEavAttributeDocument()],
            ['attribute_id']
        )->where('ea.attribute_code IN (?)', $attributeCodes
        )->where('ea.entity_type_id = 4');
        return $select->getAdapter()->fetchCol($select);
    }

    /**
     * @return int
     */
    protected function getIterationsCount()
    {
        return count(array_keys($this->helper->getProductDestinationDocumentFields()));
    }
}
