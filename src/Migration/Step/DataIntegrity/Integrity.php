<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\DataIntegrity;

use Migration\Config;
use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar\LogLevelProcessor;
use Migration\Logger\Logger;
use Migration\Logger\Manager as LogManager;
use Migration\ResourceModel\AdapterInterface;
use Migration\ResourceModel\Source;
use Migration\Step\DatabaseStage;
use Migration\Step\DataIntegrity\Model\OrphanRecordsCheckerFactory;
use Migration\Step\DataIntegrity\Model\OrphanRecordsChecker;

/**
 * Class Integrity
 */
class Integrity extends DatabaseStage implements StageInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var OrphanRecordsCheckerFactory
     */
    protected $checkerFactory;

    /**
     * @param Config $config
     * @param Logger $logger
     * @param LogLevelProcessor $progress
     * @param Source $source
     * @param OrphanRecordsCheckerFactory $checkerFactory
     */
    public function __construct(
        Config $config,
        Logger $logger,
        LogLevelProcessor $progress,
        Source $source,
        OrphanRecordsCheckerFactory $checkerFactory
    ) {
        parent::__construct($config);
        $this->logger = $logger;
        $this->progress = $progress;
        $this->source = $source;
        $this->checkerFactory = $checkerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $documentList = $this->getDocumentList();
        $this->progress->start(count($documentList), LogManager::LOG_LEVEL_INFO);

        $errorMessages = [];
        foreach ($documentList as $document) {
            foreach ($this->getAdapter()->getForeignKeys($document) as $keyData) {
                /** @var OrphanRecordsChecker $checker */
                $checker = $this->checkerFactory->create($this->getAdapter(), $keyData);
                if ($checker->hasOrphanRecords()) {
                    $errorMessages[] = $this->buildLogMessage($checker);
                }
            }
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);

        foreach ($errorMessages as $message) {
            $this->logger->error($message);
        }
        return empty($errorMessages);
    }

    /**
     * @return AdapterInterface
     */
    protected function getAdapter()
    {
        return $this->source->getAdapter();
    }

    /**
     * @return array
     */
    protected function getDocumentList()
    {
        return $this->getAdapter()->getDocumentList();
    }

    /**
     * Builds and returns well-formed error message
     *
     * @param OrphanRecordsChecker $checker
     * @return string
     */
    private function buildLogMessage(OrphanRecordsChecker $checker)
    {
        return sprintf(
            'Foreign key (%s) constraint fails. Orphan records id: %s from `%s`.`%s` has no referenced records in `%s`',
            $checker->getKeyName(),
            implode(',', $checker->getOrphanRecordsIds()),
            $checker->getChildTable(),
            $checker->getChildTableField(),
            $checker->getParentTable()
        );
    }
}
