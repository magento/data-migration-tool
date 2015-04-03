<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\App\Step\StepInterface;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderMain;
use Migration\Logger\Logger;
use Migration\ProgressBar;
use Migration\Resource\Destination;
use Migration\Resource\Source;
use Migration\Resource;
use Migration\Resource\Document;
use Migration\Resource\Record;

/**
 * Class Settings
 */
class Settings implements StepInterface
{
    const CONFIG_TABLE_NAME_SOURCE = 'core_config_data';
    const CONFIG_TABLE_NAME_DESTINATION = 'core_config_data';

    /**
     * Destination resource
     *
     * @var Destination
     */
    protected $destination;

    /**
     * Logger instance
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Progress bar
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @param Destination $destination
     * @param Source $source
     * @param Logger $logger
     * @param ProgressBar $progress
     */
    public function __construct(
        Destination $destination,
        Source $source,
        Logger $logger,
        ProgressBar $progress,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapReaderMain $mapReader
    ) {
        $this->destination = $destination;
        $this->source = $source;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->mapReader = $mapReader;
    }

    /**
     * {@inheritdoc}
     */
    public function integrity()
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->progress->start($this->getIterationsCount());
        $this->progress->advance();
        $sourceDocument = $this->source->getDocument(self::CONFIG_TABLE_NAME_SOURCE);
        $destinationName = $this->mapReader->getDocumentMap(
            self::CONFIG_TABLE_NAME_DESTINATION, MapReaderInterface::TYPE_SOURCE
        );
        $destDocument = $this->destination->getDocument($destinationName);
        $this->destination->clearDocument($destinationName);
        /** @var \Migration\RecordTransformer $recordTransformer */
        $recordTransformer = $this->recordTransformerFactory->create(
            [
                'sourceDocument' => $sourceDocument,
                'destDocument' => $destDocument,
                'mapReader' => $this->mapReader
            ]
        );
        $recordTransformer->init();
        $pageNumber = 0;
        while (!empty($bulk = $this->source->getRecords(self::CONFIG_TABLE_NAME_SOURCE, $pageNumber))) {
            $pageNumber++;
            $destinationRecords = $destDocument->getRecords();
            foreach ($bulk as $recordData) {
                /** @var Record $record */
                $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $recordData]);
                /** @var Record $destRecord */
                $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                $recordTransformer->transform($record, $destRecord);
                $destinationRecords->addRecord($destRecord);
            }
            $this->destination->saveRecords($destinationName, $destinationRecords);
        }
        $this->progress->finish();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function volumeCheck()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return "Settings step";
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->source->getDocument(self::CONFIG_TABLE_NAME_SOURCE)->getRecords());
    }
}
