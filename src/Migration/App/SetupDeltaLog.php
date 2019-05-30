<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App;

use Migration\Reader\Groups;
use Migration\App\Step\StageInterface;
use Migration\ResourceModel\Source;
use Migration\Logger\Logger;

/**
 * Class SetupDeltaLog
 */
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
     * @var \Migration\Reader\GroupsFactory
     */
    private $groupsFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Source $source
     * @param \Migration\Reader\GroupsFactory $groupsFactory
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     */
    public function __construct(
        Source $source,
        \Migration\Reader\GroupsFactory $groupsFactory,
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger
    ) {
        $this->source = $source;
        $this->groupsFactory = $groupsFactory;
        $this->progress = $progress;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $countDeltaDocuments = 0;
        $countDeltaDocumentsCreated = 0;
        $deltaLogs = [];
        $deltaLogsGroups = $this->getGroupsReader()->getGroups();
        $this->progress->start(count($deltaLogsGroups, 1) - count($deltaLogsGroups));
        /**
         * Eliminate duplicates
         */
        foreach ($deltaLogsGroups as $deltaDocuments) {
            foreach ($deltaDocuments as $documentName => $idKeys) {
                $deltaLogs[$documentName] = explode(',', $idKeys);
            }
        }
        foreach ($deltaLogs as $documentName => $idKeys) {
            $this->progress->advance();
            $countDeltaDocuments++;
            if ($this->source->getDocument($documentName)) {
                $countDeltaDocumentsCreated += (int) $this->source->createDelta($documentName, $idKeys);
            }
        }
        $this->progress->finish();
        if ($countDeltaDocuments != $countDeltaDocumentsCreated) {
            $this->logger->warning(
                sprintf(
                    'Some of the delta log tables were not created. Expected:%s. Actual:%s',
                    $countDeltaDocuments,
                    $countDeltaDocumentsCreated
                )
            );
        }
        return true;
    }

    /**
     * Get groups reader
     *
     * @return Groups
     */
    private function getGroupsReader()
    {
        if (null == $this->groupsReader) {
            $this->groupsReader = $this->groupsFactory->create('delta_document_groups_file');
        }
        return $this->groupsReader;
    }
}
