<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Migration\ResourceModel;
use Migration\Config;

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
     * @param Config $config
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Config $config,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        $mapConfigOption = 'customer_attr_map_file'
    ) {
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
        $this->groups = $groupsFactory->create('customer_attr_document_groups_file');
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $srcDocuments = array_keys($this->groups->getGroup('source_documents'));

        $dstDocuments = [];
        foreach ($srcDocuments as $sourceDocumentName) {
            $dstDocuments[] = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
        }

        $this->check($srcDocuments, MapInterface::TYPE_SOURCE, false);
        $this->check($dstDocuments, MapInterface::TYPE_DEST, false);
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * @inheritdoc
     */
    protected function getIterationsCount()
    {
        return count($this->groups->getGroup('source_documents')) * 2;
    }
}
