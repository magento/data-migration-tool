<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Logger\Logger;
use Migration\MapReader\MapReaderMain;
use Migration\MapReaderInterface;
use Migration\Resource\Source;
use Migration\Resource;

class Delta
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapReaderMain
     */
    protected $mapReader;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var Migrate
     */
    protected $migrate;

    /**
     * @param Source $source
     * @param MapReaderMain $mapReader
     * @param Logger $logger
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param Migrate $migrate
     */
    public function __construct(
        Source $source,
        MapReaderMain $mapReader,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        Migrate $migrate
    ) {
        $this->source = $source;
        $this->mapReader = $mapReader;
        $this->logger = $logger;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->migrate = $migrate;
    }

    /**
     * @return bool
     * @throws \Migration\Exception
     */
    public function delta()
    {
        $sourceDocuments = array_flip($this->source->getDocumentList());
        $deltaDocuments = $this->mapReader->getDeltaDocuments($this->source->getDocumentList());
        foreach ($deltaDocuments as $documentName => $idKey) {
            $changeLogName = $this->source->getChangeLogName($documentName);
            if (!isset($sourceDocuments[$changeLogName])) {
                throw new \Migration\Exception(sprintf('Changelog table %s is not installed', $changeLogName));
            }
            $items = $this->source->getChangedRecords($documentName, $idKey);
            if (empty($items)) {
                continue;
            }
            $sourceDocument = $this->source->getDocument($documentName);

            $destinationName = $this->mapReader->getDocumentMap($documentName, MapReaderInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }

            $this->logger->debug(sprintf(PHP_EOL . '%s have changes', $documentName));

            $destDocument = $this->destination->getDocument($destinationName);

            $recordTransformer = $this->migrate->getRecordTransformer($sourceDocument, $destDocument);
            do {
                $destinationRecords = $destDocument->getRecords();
                $ids = [];
                $deleteRecords = [];
                foreach ($items as $data) {
                    echo('.');
                    $operation = strtoupper($data['operation']);
                    if ($operation == 'DELETE') {
                        $deleteRecords[] = $data["old_" . $idKey];
                        $ids[] = $data["old_" . $idKey];
                        continue;
                    }
                    unset($data['operation']);
                    unset($data["old_" . $idKey]);
                    $ids[] = $data[$idKey];

                    /** @var Resource\Record $record */
                    /** @var Resource\Record $destRecord */
                    if ($recordTransformer) {
                        $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
                        $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                        $recordTransformer->transform($record, $destRecord);
                    } else {
                        $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
                    }
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->updateChangedRecords($destinationName, $destinationRecords);
                if (!empty($deleteRecords)) {
                    $this->destination->deleteRecords(
                        $this->destination->addDocumentPrefix($documentName),
                        $idKey,
                        $deleteRecords
                    );
                }
                $this->source->deleteRecords($changeLogName, $idKey, $ids);
            } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
        }
        return true;
    }
}
