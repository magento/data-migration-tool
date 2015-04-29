<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\App\Step\AbstractIntegrity;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\Groups;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\Resource;
use Migration\Logger\Manager as LogManager;

/**
 * Class Integrity
 */
class Integrity extends AbstractIntegrity
{
    /**
     * @var Groups
     */
    protected $groups;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        $mapConfigOption = 'customer_attr_map_file'
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
        $this->groups = $groupsFactory->create('customer_attr_document_groups_file');
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount(), LogManager::LOG_LEVEL_INFO);
        $srcDocuments = array_keys($this->groups->getGroup('source_documents'));

        $dstDocuments = [];
        foreach ($srcDocuments as $sourceDocumentName) {
            $dstDocuments[] = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
        }

        $this->check($srcDocuments, MapInterface::TYPE_SOURCE);
        $this->check($dstDocuments, MapInterface::TYPE_DEST);
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return $this->checkForErrors();
    }

    /**
     * {@inheritdoc}
     */
    protected function getIterationsCount()
    {
        return count($this->groups->getGroup('source_documents')) * 2;
    }
}
