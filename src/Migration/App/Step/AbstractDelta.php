<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\App\Step;

use Migration\Logger\Logger;
use Migration\MapReaderInterface;
use Migration\Resource\Source;
use Migration\Resource;

abstract class AbstractDelta
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var MapReaderInterface
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
     * @param Source $source
     * @param MapReaderInterface $mapReader
     * @param Logger $logger
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     */
    public function __construct(
        Source $source,
        MapReaderInterface $mapReader,
        Logger $logger,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory
    ) {
        $this->source = $source;
        $this->mapReader = $mapReader;
        $this->logger = $logger;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
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
                throw new \Migration\Exception(sprintf('Changelog for %s is not installed', $documentName));
            }

            $destinationName = $this->mapReader->getDocumentMap($documentName, MapReaderInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }

            if ($this->source->getRecordsCount($changeLogName, false) == 0) {
                continue;
            }
            $this->logger->debug(sprintf(PHP_EOL . '%s have changes', $documentName));

            $this->processDeletedRecords($documentName, $idKey, $destinationName);
            $this->processChangedRecords($documentName, $idKey);
        }
        return true;
    }

    /**
     * @param string $documentName
     * @param string $idKey
     * @param string $destinationName
     * @return void
     */
    protected function processDeletedRecords($documentName, $idKey, $destinationName)
    {
        while (!empty($items = $this->source->getDeletedRecords($documentName, $idKey))) {
            $this->destination->deleteRecords(
                $this->destination->addDocumentPrefix($destinationName),
                $idKey,
                $items
            );
            $this->source->deleteRecords($this->source->getChangeLogName($documentName), $idKey, $items);
        }
    }

    /**
     * @param string $documentName
     * @param string $idKey
     * @return void
     */
    protected function processChangedRecords($documentName, $idKey)
    {
        $items = $this->source->getChangedRecords($documentName, $idKey);
        if (empty($items)) {
            return;
        }
        $destinationName = $this->mapReader->getDocumentMap($documentName, MapReaderInterface::TYPE_SOURCE);

        $sourceDocument = $this->source->getDocument($documentName);
        $destDocument = $this->destination->getDocument($destinationName);

        $recordTransformer = $this->getRecordTransformer($sourceDocument, $destDocument);
        do {
            $destinationRecords = $destDocument->getRecords();

            $ids = [];

            foreach ($items as $data) {
                echo('.');
                $ids[] = $data[$idKey];

                $this->transformData(
                    $data,
                    $sourceDocument,
                    $destDocument,
                    $recordTransformer,
                    $destinationRecords
                );
            }

            $this->destination->updateChangedRecords($destinationName, $destinationRecords);
            $this->source->deleteRecords($this->source->getChangeLogName($documentName), $idKey, $ids);
        } while (!empty($items = $this->source->getChangedRecords($documentName, $idKey)));
    }

    /**
     * @param array $data
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destDocument
     * @param \Migration\RecordTransformer $recordTransformer
     * @param Resource\Record\Collection $destinationRecords
     * @return void
     */
    protected function transformData($data, $sourceDocument, $destDocument, $recordTransformer, $destinationRecords)
    {
        if ($recordTransformer) {
            $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
            $destRecord = $this->recordFactory->create(['document' => $destDocument]);
            $recordTransformer->transform($record, $destRecord);
        } else {
            $destRecord = $this->recordFactory->create(['document' => $destDocument, 'data' => $data]);
        }
        $destinationRecords->addRecord($destRecord);
    }

    /**
     * @param Resource\Document $sourceDocument
     * @param Resource\Document $destinationDocument
     * @return \Migration\RecordTransformer
     */
    protected function getRecordTransformer($sourceDocument, $destinationDocument)
    {
        $recordTransformer = $this->recordTransformerFactory->create(
            [
                'sourceDocument' => $sourceDocument,
                'destDocument' => $destinationDocument,
                'mapReader' => $this->mapReader
            ]
        );
        $recordTransformer->init();
        return $recordTransformer;
    }
}
