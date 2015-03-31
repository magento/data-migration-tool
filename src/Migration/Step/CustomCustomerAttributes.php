<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step;

use Migration\Logger\Logger;
use Migration\ProgressBar;
use Migration\Resource\Destination;
use Migration\Resource\Document;
use Migration\Resource\Record;
use Migration\Resource\RecordFactory;
use Migration\Resource\Source;
use Migration\Config;
use Migration\App\Step\DeltaInterface;
use Migration\App\Step\RollbackInterface;

/**
 * Class CustomerAttributesSalesFlat
 */
class CustomCustomerAttributes extends DatabaseStep implements DeltaInterface, RollbackInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * @var RecordFactory
     */
    protected $factory;

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
        parent::__construct($config);
        $this->source = $source;
        $this->destination = $destination;
        $this->progress = $progress;
        $this->factory = $factory;
        $this->logger = $logger;
    }

    /**
     * Integrity check
     *
     * @return bool
     */
    public function integrity()
    {
        $result = true;
        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            $result &= (bool)$this->source->getDocument($sourceName);
            $result &= (bool)$this->destination->getDocument($destinationName);
        }
        $this->progress->finish();
        return (bool)$result;
    }

    /**
     * Run step
     *
     * @return bool
     */
    public function run()
    {
        /** @var \Migration\Resource\Adapter\Mysql $sourceAdapter */
        $sourceAdapter = $this->source->getAdapter();
        /** @var \Migration\Resource\Adapter\Mysql $destinationAdapter */
        $destinationAdapter = $this->destination->getAdapter();

        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceDocumentName => $destinationDocumentName) {
            $this->progress->advance();

            $sourceTable =  $sourceAdapter->getTableDdlCopy(
                $this->source->addDocumentPrefix($sourceDocumentName),
                $this->destination->addDocumentPrefix($destinationDocumentName)
            );
            $destinationTable = $destinationAdapter->getTableDdlCopy(
                $this->destination->addDocumentPrefix($destinationDocumentName),
                $this->destination->addDocumentPrefix($destinationDocumentName)
            );
            foreach ($sourceTable->getColumns() as $columnData) {
                $destinationTable->setColumn($columnData);
            }
            $destinationAdapter->createTableByDdl($destinationTable);

            $destinationDocument = $this->destination->getDocument($destinationDocumentName);
            $pageNumber = 0;
            while (!empty($sourceRecords = $this->source->getRecords($sourceDocumentName, $pageNumber))) {
                $pageNumber++;
                $recordsToSave = $destinationDocument->getRecords();
                foreach ($sourceRecords as $recordData) {
                    /** @var Record $destinationRecord */
                    $destinationRecord = $this->factory->create(['document' => $destinationDocument]);
                    $destinationRecord->setData($recordData);
                    $recordsToSave->addRecord($destinationRecord);
                }
                $this->destination->saveRecords($destinationDocument->getName(), $recordsToSave);
            };
        }
        $this->progress->finish();
        return true;
    }

    /**
     * Volume check
     *
     * @return bool
     */
    public function volumeCheck()
    {
        $result = true;
        $this->progress->start(count($this->getDocumentList()));
        foreach ($this->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            $sourceFields = $this->source->getDocument($sourceName)->getStructure()->getFields();
            $destinationFields = $this->destination->getDocument($destinationName)->getStructure()->getFields();
            $result &= empty(array_diff_key($sourceFields, $destinationFields));
            $result &= $this->source->getRecordsCount($sourceName) ==
                $this->destination->getRecordsCount($destinationName);
        }
        $this->progress->finish();
        return (bool)$result;
    }

    /**
     * Get step title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Custom Customer Attributes Step';
    }

    /**
     * @return array
     */
    public function getDocumentList()
    {
        return [
            'enterprise_customer_sales_flat_order' =>
                'magento_customercustomattributes_sales_flat_order',

            'enterprise_customer_sales_flat_order_address' =>
                'magento_customercustomattributes_sales_flat_order_address',

            'enterprise_customer_sales_flat_quote' =>
                'magento_customercustomattributes_sales_flat_quote',

            'enterprise_customer_sales_flat_quote_address' =>
                'magento_customercustomattributes_sales_flat_quote_address'
        ];
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

    /**
     * @inheritdoc
     */
    public function delta()
    {
        $sourceDocuments = array_flip($this->source->getDocumentList());
        $documentsMap = $this->getDocumentList();
        foreach ($this->getDeltaDocuments() as $sourceDocumentName => $idKey) {
            $changeLogName = $this->source->getChangeLogName($sourceDocumentName);
            if (!isset($sourceDocuments[$changeLogName])) {
                throw new \Migration\Exception(sprintf('Changelog for %s is not installed', $sourceDocumentName));
            }
            if ($this->source->getRecordsCount($changeLogName, false) == 0) {
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
                $this->source->deleteRecords($this->source->getChangeLogName($sourceDocumentName), $idKey, $ids);
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
            $this->source->deleteRecords($this->source->getChangeLogName($documentName), $idKey, $items);
        }
    }

    /**
     * @inheritdoc
     */
    public function rollback()
    {
        return true;
    }
}
