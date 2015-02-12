<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Handler;
use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\Resource;

/**
 * Class Example
 */
class Map extends AbstractStep
{
    /**
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var Resource\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var MapReader
     */
    protected $mapReader;

    /**
     * @var Handler\ManagerFactory
     */
    protected $handlerManagerFactory;

    /**
     * @param Progress $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Resource\RecordFactory $recordFactory
     * @param Handler\ManagerFactory $handlerManagerFactory
     * @param MapReader $mapReader
     * @throws \Exception
     */
    public function __construct(
        Progress $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        Resource\RecordFactory $recordFactory,
        Handler\ManagerFactory $handlerManagerFactory,
        MapReader $mapReader
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->handlerManagerFactory = $handlerManagerFactory;
        $this->mapReader = $mapReader;
        $this->mapReader->init();
        parent::__construct($progress, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        parent::run();
        $documentListSource = $this->source->getDocumentList();
        /** @var Resource\Document $sourceDocument */
        foreach ($documentListSource as $sourceName) {
            $sourceDocument = $this->source->getDocument($sourceName);
            $destinationName = $this->mapReader->getDocumentMap($sourceName, MapReader::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destinationDocument = $this->destination->getDocument($destinationName);
            if (!$destinationDocument) {
                throw new \Exception("Failed to find destination document with name '$destinationName'.");
            }

            /** @var Handler\Manager $handlerManager */
            $handlerManager = $this->handlerManagerFactory->create();
            $handlerManager->init($sourceDocument, $destinationDocument);
            $pageNumber = 0;
            while (!empty($bulk = $this->source->getRecords($sourceName, $pageNumber))) {
                $pageNumber++;
                $recordsCollection = $sourceDocument->getRecords();
                foreach ($bulk as $recordData) {
                    $record = $this->recordFactory->create(['data' => $recordData]);
                    $recordsCollection->addRecord($record);
                }
                $destCollection = $handlerManager->process($recordsCollection);
                $this->destination->saveRecords($destinationName, $destCollection);
            }
            $this->progress->advance();
        }

        $this->progress->finish();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSteps()
    {
        return count($this->source->getDocumentList());
    }
}
