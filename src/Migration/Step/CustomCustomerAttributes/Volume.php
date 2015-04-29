<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar;
use Migration\Reader\Groups;
use Migration\Reader\GroupsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\Resource\Destination;
use Migration\Resource\Source;
use Migration\Logger\Manager as LogManager;

/**
 * Class Volume
 */
class Volume implements StageInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var Groups
     */
    protected $groups;

    /**
     * @param Source $source
     * @param Destination $destination
     * @param ProgressBar\LogLevelProcessor $progress
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        Source $source,
        Destination $destination,
        ProgressBar\LogLevelProcessor $progress,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progress = $progress;
        $this->groups = $groupsFactory->create('customer_attr_document_groups_file');
        $this->map = $mapFactory->create('customer_attr_map_file');
    }

    /**
     * Volume check
     *
     * @return bool
     */
    public function perform()
    {
        $result = true;
        $sourceDocuments = array_keys($this->groups->getGroup('source_documents'));
        $this->progress->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);
        foreach ($sourceDocuments as $sourceName) {
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
            $destinationName = $this->map->getDocumentMap($sourceName, MapInterface::TYPE_SOURCE);

            $sourceFields = $this->source->getDocument($sourceName)->getStructure()->getFields();
            $destinationFields = $this->destination->getDocument($destinationName)->getStructure()->getFields();
            $result &= empty(array_diff_key($sourceFields, $destinationFields));

            $result &= $this->source->getRecordsCount($sourceName) ==
                $this->destination->getRecordsCount($destinationName);
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return (bool)$result;
    }
}
