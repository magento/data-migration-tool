<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\ListsFactory;
use Migration\Reader\MapFactory;
use Migration\ProgressBar;
use Migration\Resource;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Lists
     */
    protected $lists;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapFactory $mapFactory
     * @param ListsFactory $listsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        ListsFactory $listsFactory,
        $mapConfigOption = 'eav_map_file'
    ) {
        $this->lists = $listsFactory->create('eav_list_file');
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());

        $documents = $this->lists->getList('documents');
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
        return count($this->lists->getList('documents')) * 2;
    }
}
