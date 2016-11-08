<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\DataIntegrity;

use Migration\Config;
use Migration\App\Step\StageInterface;
use Migration\App\ProgressBar\LogLevelProcessor;
use Migration\Logger\Logger;
use Migration\Logger\Manager as LogManager;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;
use Migration\Step\DatabaseStage;
use Migration\Step\DataIntegrity\Model\ForeignKeyFactory;
use Migration\Step\DataIntegrity\Model\ForeignKey;

/**
 * Class Integrity
 */
class Integrity extends DatabaseStage implements StageInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var LogLevelProcessor
     */
    private $progress;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var ForeignKeyFactory
     */
    private $foreignKeyFactory;

    /**
     * @param Config $config
     * @param Logger $logger
     * @param LogLevelProcessor $progress
     * @param Source $source
     * @param ForeignKeyFactory $foreignKeyFactory
     */
    public function __construct(
        Config $config,
        Logger $logger,
        LogLevelProcessor $progress,
        Source $source,
        ForeignKeyFactory $foreignKeyFactory
    ) {
        parent::__construct($config);
        $this->logger = $logger;
        $this->progress = $progress;
        $this->source = $source;
        $this->foreignKeyFactory = $foreignKeyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $adapter = $this->source->getAdapter();
        if (!$adapter instanceof Mysql) {
            return true;
        }
        $documentList = $adapter->getDocumentList();
        $this->progress->start(count($documentList), LogManager::LOG_LEVEL_INFO);

        $errorMessages = [];
        foreach ($documentList as $document) {
            $foreignKeys = $adapter->getForeignKeys($document);
            $foreignKeysCount = count($foreignKeys);

            if ($foreignKeysCount) {
                $this->progress->start($foreignKeysCount, LogManager::LOG_LEVEL_DEBUG);
                foreach ($foreignKeys as $keyData) {
                    /** @var ForeignKey $foreignKey */
                    $foreignKey = $this->foreignKeyFactory->create($adapter, $keyData);
                    if ($foreignKey->getOrphanedRowIds()) {
                        $errorMessages[] = $this->buildLogMessage($foreignKey);
                    }
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                }
                $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
            }
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
        }
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);

        foreach ($errorMessages as $message) {
            $this->logger->error($message);
        }
        return empty($errorMessages);
    }

    private function buildLogMessage(ForeignKey $foreignKey)
    {
        return sprintf(
            'Foreign key (%s) constraint fails. Orphaned records: `%s`.`%s` IN (%s) has no referenced records in `%s`',
            $foreignKey->getKeyName(),
            $foreignKey->getChildTable(),
            $foreignKey->getChildTableKey(),
            implode(',', $foreignKey->getOrphanedRowIds()),
            $foreignKey->getParentTable()
        );
    }
}
