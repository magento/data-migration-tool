<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\StageInterface;
use Migration\Handler;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\ResourceModel\Record;
use Migration\App\ProgressBar;
use Migration\Logger\Manager as LogManager;
use Migration\Logger\Logger;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data implements StageInterface
{
    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var ResourceModel\RecordFactory
     */
    protected $recordFactory;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var \Migration\RecordTransformerFactory
     */
    protected $recordTransformerFactory;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var AdapterInterface
     */
    protected $sourceAdapter;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('log_map_file');
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('log_document_groups_file');
        $this->logger = $logger;
        $this->sourceAdapter = $this->source->getAdapter();
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getBulksCount(), LogManager::LOG_LEVEL_INFO);
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        foreach ($sourceDocuments as $sourceDocName) {
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);

            $pageNumber = 0;
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $this->progress->start($this->source->getRecordsCount($sourceDocName), LogManager::LOG_LEVEL_DEBUG);

            $sourceDocumentName = $sourceDocument->getName();
            /** @var \Magento\Framework\DB\Select $select */
            $select = $this->getLogDataSelect();
            while (!empty($bulk = $this->getRecords($sourceDocumentName, $select, $pageNumber))) {
                $pageNumber++;
                $destinationRecords = $destDocument->getRecords();
                foreach ($bulk as $recordData) {
                    $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create([
                        'document'  => $destDocument,
                        'data'      => $recordData,
                    ]);
                    $destinationRecords->addRecord($destRecord);
                }
                $this->destination->saveRecords($destinationName, $destinationRecords);
                $this->progress->advance(LogManager::LOG_LEVEL_INFO);
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->clearLog(array_keys($this->readerGroups->getGroup('destination_documents_to_clear')));
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }

    /**
     * Get records
     *
     * @param string $sourceDocumentName
     * @param \Magento\Framework\DB\Select $select
     * @param int $pageNumber
     * @return array
     */
    protected function getRecords($sourceDocumentName, \Magento\Framework\DB\Select $select, $pageNumber)
    {
        $select->limit(
            $this->source->getPageSize($sourceDocumentName),
            $pageNumber * $this->source->getPageSize($sourceDocumentName)
        );

        return $this->sourceAdapter->loadDataFromSelect($select);
    }

    /**
     * Get log data select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getLogDataSelect()
    {

        $fields = [
            'visitor_id'    => 'lv.visitor_id',
            'customer_id'   => 'lc.customer_id',
            'session_id'    => 'lv.session_id',
            'last_visit_at' => 'lv.last_visit_at',
        ];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['lv' => $this->source->addDocumentPrefix('log_visitor')], $fields)
            ->joinLeft(
                ['lc' => $this->source->addDocumentPrefix('log_customer')],
                'lv.visitor_id = lc.visitor_id',
                []
            )
            ->group('lv.visitor_id')
            ->order('lv.visitor_id');
        
        return $select;
    }

    /**
     * Clear log
     *
     * @param array $documents
     * @return void
     */
    protected function clearLog($documents)
    {
        foreach ($documents as $documentName) {
            $this->progress->advance();
            $this->destination->clearDocument($documentName);
        }
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getBulksCount()
    {
        $iterations = 0;
        foreach (array_keys($this->readerGroups->getGroup('source_documents')) as $document) {
            $iterations += ceil($this->source->getRecordsCount($document) / $this->source->getPageSize($document));
        }

        return count($this->readerGroups->getGroup('destination_documents_to_clear'))
            + $iterations;
    }
}
