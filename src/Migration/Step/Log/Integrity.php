<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\App\ProgressBar;
use Migration\ResourceModel;
use Migration\Config;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

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
        $mapConfigOption = 'log_map_file'
    ) {
        $this->readerGroups = $groupsFactory->create('log_document_groups_file');
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * @inheritdoc
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

        $dstDocumentList = array_flip($this->destination->getDocumentList());
        foreach (array_keys($this->readerGroups->getGroup('destination_documents_to_clear')) as $document) {
            $this->progress->advance();
            if (!isset($dstDocumentList[$document])) {
                $this->missingDocuments[MapInterface::TYPE_DEST][$document] = true;
            }
        }

        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * @inheritdoc
     */
    protected function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('destination_documents_to_clear'))
            + count($this->readerGroups->getGroup('source_documents')) * 2;
    }
}
