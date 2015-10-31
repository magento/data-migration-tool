<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\App\ProgressBar;
use Migration\ResourceModel;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Groups
     */
    protected $groups;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        $mapConfigOption = 'eav_map_file'
    ) {
        $this->groups = $groupsFactory->create('eav_document_groups_file');
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());

        $documents = array_keys($this->groups->getGroup('documents'));
        foreach ($documents as $sourceDocumentName) {
            $this->check([$sourceDocumentName], MapInterface::TYPE_SOURCE);
            $destinationDocumentName = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
            $this->check([$destinationDocumentName], MapInterface::TYPE_DEST);
        }

        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Returns number of iterations for integrity check
     * @return mixed
     */
    protected function getIterationsCount()
    {
        return count($this->groups->getGroup('documents')) * 2;
    }
}
