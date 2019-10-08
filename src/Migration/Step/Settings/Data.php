<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Settings;

use Migration\App\Step\StageInterface;
use Migration\Reader\Settings as ReaderSettings;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
use Migration\ResourceModel;
use Migration\ResourceModel\Document;
use Migration\ResourceModel\Record;
use Migration\Handler;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data implements StageInterface
{
    const CONFIG_TABLE_NAME_SOURCE = 'core_config_data';
    const CONFIG_TABLE_NAME_DESTINATION = 'core_config_data';
    const CONFIG_FIELD_CONFIG_ID = 'config_id';
    const CONFIG_FIELD_SCOPE_ID = 'scope_id';
    const CONFIG_FIELD_SCOPE = 'scope';
    const CONFIG_FIELD_PATH = 'path';
    const CONFIG_FIELD_VALUE = 'value';
    const CONFIG_FIELD_UPDATED_AT = 'updated_at';

    /**
     * @var array
     */
    protected $configTableSchema = [
        self::CONFIG_FIELD_CONFIG_ID,
        self::CONFIG_FIELD_SCOPE,
        self::CONFIG_FIELD_SCOPE_ID,
        self::CONFIG_FIELD_PATH,
        self::CONFIG_FIELD_VALUE
    ];

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var ReaderSettings
     */
    protected $readerSettings;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Handler\ManagerFactory
     */
    protected $handlerManagerFactory;

    /**
     * @param Destination $destination
     * @param Source $source
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\RecordFactory $recordFactory
     * @param ReaderSettings $readerSettings
     * @param Handler\ManagerFactory $handlerManagerFactory
     */
    public function __construct(
        Destination $destination,
        Source $source,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\RecordFactory $recordFactory,
        ReaderSettings $readerSettings,
        Handler\ManagerFactory $handlerManagerFactory
    ) {
        $this->destination = $destination;
        $this->source = $source;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->readerSettings = $readerSettings;
        $this->handlerManagerFactory = $handlerManagerFactory;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $destinationDocument = $this->destination->getDocument(self::CONFIG_TABLE_NAME_DESTINATION);
        $recordsCountSource = $this->source->getRecordsCount(self::CONFIG_TABLE_NAME_SOURCE);
        $recordsCountDestination = $this->destination->getRecordsCount(self::CONFIG_TABLE_NAME_DESTINATION);
        $this->progress->start($recordsCountSource);
        $sourceRecords = $this->source->getRecords(
            self::CONFIG_TABLE_NAME_SOURCE,
            0,
            $recordsCountSource
        );
        $destinationRecords = $this->destination->getRecords(
            self::CONFIG_TABLE_NAME_DESTINATION,
            0,
            $recordsCountDestination
        );
        foreach ($sourceRecords as $sourceRecord) {
            $this->progress->advance();
            $sourceRecord = array_intersect_key($sourceRecord, array_flip($this->configTableSchema));
            if (!$this->readerSettings->isNodeIgnored($sourceRecord[self::CONFIG_FIELD_PATH])) {
                $sourceRecordPathMapped = $this->readerSettings->getNodeMap($sourceRecord[self::CONFIG_FIELD_PATH]);
                foreach ($destinationRecords as &$destinationRecord) {
                    if ($destinationRecord[self::CONFIG_FIELD_SCOPE] == $sourceRecord[self::CONFIG_FIELD_SCOPE]
                        && $destinationRecord[self::CONFIG_FIELD_SCOPE_ID] == $sourceRecord[self::CONFIG_FIELD_SCOPE_ID]
                        && $destinationRecord[self::CONFIG_FIELD_PATH] == $sourceRecordPathMapped
                    ) {
                        $record = $this->applyHandler($destinationDocument, $sourceRecord, $destinationRecord);
                        $destinationRecord[self::CONFIG_FIELD_VALUE] = $record->getValue(self::CONFIG_FIELD_VALUE);
                        continue 2;
                    }
                }
                $record = $this->applyHandler($destinationDocument, $sourceRecord, []);
                $record->setValue(self::CONFIG_FIELD_PATH, $sourceRecordPathMapped);
                $destinationRecords[] = $record->getData();
            }
        }
        foreach ($destinationRecords as &$destinationRecord) {
            unset($destinationRecord[self::CONFIG_FIELD_CONFIG_ID]);
            $destinationRecord[self::CONFIG_FIELD_UPDATED_AT] = null;
        }
        $this->destination->clearDocument(self::CONFIG_TABLE_NAME_DESTINATION);
        $this->destination->saveRecords(self::CONFIG_TABLE_NAME_DESTINATION, $destinationRecords);
        $this->progress->finish();
        return true;
    }

    /**
     * Apply handler
     *
     * @param Document $document
     * @param array $sourceData
     * @param array $destinationData
     * @return Record
     */
    protected function applyHandler(
        \Migration\ResourceModel\Document $document,
        array $sourceData,
        array $destinationData
    ) {
        /** @var Record $sourceRecord */
        $sourceRecord = $this->recordFactory->create(['document' => $document, 'data' => $sourceData]);
        /** @var Record $destinationData */
        $destinationRecord = $this->recordFactory->create(['document' => $document, 'data' => $destinationData]);
        $handler = $this->getHandler($sourceData[self::CONFIG_FIELD_PATH]);
        if ($handler) {
            $handler->handle($sourceRecord, $destinationRecord);
        }
        return $sourceRecord;
    }

    /**
     * Get handler
     *
     * @param string $path
     * @return bool|Handler\HandlerInterface|null
     * @throws \Migration\Exception
     */
    protected function getHandler($path)
    {
        $handlerConfig = $this->readerSettings->getValueHandler($path);
        if (!$handlerConfig) {
            return false;
        }
        /** @var Handler\Manager $handlerManager */
        $handlerManager = $this->handlerManagerFactory->create();
        $handlerManager->initHandler(self::CONFIG_FIELD_VALUE, $handlerConfig, $path);
        return $handlerManager->getHandler($path);
    }
}
