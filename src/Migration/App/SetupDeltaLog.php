<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Migration\Reader\Groups;
use Migration\App\Step\StageInterface;
use Migration\Resource\Source;
use Migration\Logger\Manager as LogManager;

class SetupDeltaLog implements StageInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Groups
     */
    protected $groupsReader;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @param Source $source
     * @param \Migration\Reader\GroupsFactory $groupsFactory
     * @param ProgressBar\LogLevelProcessor $progress
     */
    public function __construct(
        Source $source,
        \Migration\Reader\GroupsFactory $groupsFactory,
        ProgressBar\LogLevelProcessor $progress
    ) {
        $this->source = $source;
        $this->groupsReader = $groupsFactory->create('delta_document_groups_file');
        $this->progress = $progress;
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $deltaLogs = $this->groupsReader->getGroups();
        $this->progress->start(count($deltaLogs, 1) - count($deltaLogs), LogManager::LOG_LEVEL_INFO);
        foreach ($deltaLogs as $deltaDocuments) {
            foreach ($deltaDocuments as $documentName => $idKey) {
                $this->progress->advance(LogManager::LOG_LEVEL_INFO);
                $this->source->createDelta($documentName, $idKey);
            }
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }
}
