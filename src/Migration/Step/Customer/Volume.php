<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
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
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param ProgressBar\LogLevelProcessor $progress
     * @param GroupsFactory $groupsFactory
     * @param Helper $helper
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        ProgressBar\LogLevelProcessor $progress,
        GroupsFactory $groupsFactory,
        Helper $helper
    ) {
        $this->helper = $helper;
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('customer_map_file');
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        parent::__construct($logger);
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        $this->progress->start($this->getIterationsCount());
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $sourceCount = $this->helper->getSourceRecordsCount($sourceDocName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $this->errors[] = 'Mismatch of entities in the document: ' . $destinationName;
            }
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('source_documents'));
    }
}
