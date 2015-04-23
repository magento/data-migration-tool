<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\CustomCustomerAttributes;

use Migration\App\Step\StageInterface;
use Migration\Config;
use Migration\Resource\Source;
use Migration\Resource\Destination;
use Migration\App\ProgressBar;
use Migration\Resource\RecordFactory;
use Migration\Logger\Logger;
use Migration\Resource\Record;
use Migration\Step\CustomCustomerAttributes;

/**
 * Class CustomerAttributesSalesFlat
 */
class Delta extends CustomCustomerAttributes implements StageInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Documents for delta monitoring with id columns
     *
     * @var array
     */
    protected $deltaDocuments = [];

    /**
     * @param Config $config
     * @param Source $source
     * @param Destination $destination
     * @param ProgressBar $progress
     * @param RecordFactory $factory
     * @param Logger $logger
     * @throws \Migration\Exception
     */
    public function __construct(
        Config $config,
        Source $source,
        Destination $destination,
        ProgressBar $progress,
        RecordFactory $factory,
        Logger $logger
    ) {
        parent::__construct($config, $source, $destination, $progress);
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = array_flip($this->source->getDocumentList());
        $documentsMap = $this->getDocumentList();
        foreach ($this->getDeltaDocuments() as $sourceDocumentName => $idKey) {
            $deltaLogName = $this->source->getDeltaLogName($sourceDocumentName);
            if (!isset($sourceDocuments[$deltaLogName])) {
                throw new \Migration\Exception(sprintf('Delta log for %s is not installed', $sourceDocumentName));
            }
            if ($this->source->getRecordsCount($deltaLogName, false) == 0) {
                continue;
            }
            $this->logger->debug(sprintf(PHP_EOL . '%s have changes', $sourceDocumentName));

            $destinationDocumentName = $documentsMap[$sourceDocumentName];
            $destinationDocument = $this->destination->getDocument($destinationDocumentName);

            $this->processDeletedRecords($sourceDocumentName, $idKey, $destinationDocumentName);

            while (!empty($sourceRecords = $this->source->getChangedRecords($sourceDocumentName, 'entity_id'))) {
                $recordsToSave = $destinationDocument->getRecords();
                $ids = [];
                foreach ($sourceRecords as $recordData) {
                    echo('.');
                    $ids[] = $recordData[$idKey];
                    /** @var Record $destinationRecord */
                    $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
                    $destinationRecord->setData($recordData);
                    $recordsToSave->addRecord($destinationRecord);
                }
                $this->destination->updateChangedRecords($destinationDocumentName, $recordsToSave);
                $this->source->deleteRecords($this->source->getDeltaLogName($sourceDocumentName), $idKey, $ids);
            }
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
            $this->source->deleteRecords($this->source->getDeltaLogName($documentName), $idKey, $items);
        }
    }

    /**
     * @return array
     */
    protected function getDeltaDocuments()
    {
        if (empty($this->deltaDocuments)) {
            foreach (array_keys($this->getDocumentList()) as $sourceDocument) {
                $this->deltaDocuments[$sourceDocument] = 'entity_id';
            }
        }
        return $this->deltaDocuments;
    }
}
