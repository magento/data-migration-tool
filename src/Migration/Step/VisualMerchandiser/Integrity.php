<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\VisualMerchandiser;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\App\ProgressBar;
use Migration\ResourceModel;

class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerList;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param string $mapConfigOption
     * @param string $groupMapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        $mapConfigOption = 'visual_merchandiser_map',
        $groupMapConfigOption = 'visual_merchandiser_document_groups'
    ) {
        $this->readerGroups = $groupsFactory->create($groupMapConfigOption);
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $srcDocuments = array_keys($this->readerGroups->getGroup('source_documents'));

        $dstDocuments = [];
        foreach ($srcDocuments as $sourceDocumentName) {
            $dstDocuments[] = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
        }

        $this->check($srcDocuments, MapInterface::TYPE_SOURCE);
        $this->check($dstDocuments, MapInterface::TYPE_DEST);

        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * {@inheritdoc}
     */
    protected function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('source_documents'));
    }
}
