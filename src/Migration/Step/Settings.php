<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\StageInterface;
use Migration\Reader\Settings as ReaderSettings;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Resource\Destination;
use Migration\Resource\Source;
use Migration\Resource;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\Handler;

/**
 * Class Settings
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Settings implements StageInterface
{
    const CONFIG_TABLE_NAME_SOURCE = 'core_config_data';
    const CONFIG_TABLE_NAME_DESTINATION = 'core_config_data';
    const CONFIG_FIELD_CONFIG_ID = 'config_id';
    const CONFIG_FIELD_SCOPE_ID = 'scope_id';
    const CONFIG_FIELD_SCOPE = 'scope';
    const CONFIG_FIELD_PATH = 'path';
    const CONFIG_FIELD_VALUE = 'value';

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
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Handler\ManagerFactory
     */
    protected $handlerManagerFactory;

    /**
     * @var string
     */
    protected $stage;

    /**
     * @param Destination $destination
     * @param Source $source
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Resource\RecordFactory $recordFactory
     * @param ReaderSettings $readerSettings
     * @param Handler\ManagerFactory $handlerManagerFactory
     * @param string $stage
     */
    public function __construct(
        Destination $destination,
        Source $source,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        Resource\RecordFactory $recordFactory,
        ReaderSettings $readerSettings,
        Handler\ManagerFactory $handlerManagerFactory,
        $stage
    ) {
        $this->destination = $destination;
        $this->source = $source;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->readerSettings = $readerSettings;
        $this->handlerManagerFactory = $handlerManagerFactory;
        $this->stage = $stage;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        if (!method_exists($this, $this->stage)) {
            throw new \Migration\Exception('Invalid step configuration');
        }

        return call_user_func([$this, $this->stage]);
    }

    /**
     * @return bool
     */
    protected function integrity()
    {
        $this->progress->start(1);
        $this->progress->advance();
        $documents = $this->source->getDocumentList();
        if (!in_array(self::CONFIG_TABLE_NAME_SOURCE, $documents)) {
            $this->logger->error(
                sprintf(
                    'Integrity check failed due to "%s" document does not exist in the source resource',
                    self::CONFIG_TABLE_NAME_SOURCE
                )
            );
            return false;
        }
        $documents = $this->destination->getDocumentList();
        if (!in_array(self::CONFIG_TABLE_NAME_DESTINATION, $documents)) {
            $this->logger->error(
                sprintf(
                    'Integrity check failed due to "%s" document does not exist in the destination resource',
                    self::CONFIG_TABLE_NAME_DESTINATION
                )
            );
            return false;
        }
        $this->progress->finish();
        return true;
    }

    /**
     * @return bool
     * @throws \Migration\Exception
     */
    protected function data()
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
        }
        $this->destination->clearDocument(self::CONFIG_TABLE_NAME_DESTINATION);
        $this->destination->saveRecords(self::CONFIG_TABLE_NAME_DESTINATION, $destinationRecords);
        $this->progress->finish();
        return true;
    }

    /**
     * @param Document $document
     * @param array $sourceData
     * @param array $destinationData
     * @return Record
     */
    protected function applyHandler(
        \Migration\Resource\Document $document,
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
