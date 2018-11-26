<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\VisualMerchandiser;

use Migration\App\Step\StageInterface;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
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
class Data extends \Migration\Step\DatabaseStage implements StageInterface
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
     * @var \Migration\Reader\Map
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
     * @var Logger
     */
    protected $logger;

    /**
     * @var \Migration\Step\VisualMerchandiser\Helper
     */
    protected $helper;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * Data constructor.
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\RecordFactory $recordFactory
     * @param \Migration\RecordTransformerFactory $recordTransformerFactory
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param Logger $logger
     * @param Helper $helper
     * @param \Migration\Config $config
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ResourceModel\RecordFactory $recordFactory,
        \Migration\RecordTransformerFactory $recordTransformerFactory,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        Logger $logger,
        \Migration\Step\VisualMerchandiser\Helper $helper,
        \Migration\Config $config
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->recordFactory = $recordFactory;
        $this->recordTransformerFactory = $recordTransformerFactory;
        $this->map = $mapFactory->create('visual_merchandiser_map');
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('visual_merchandiser_document_groups');
        $this->logger = $logger;
        $this->helper = $helper;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        $this->progress->start(count($sourceDocuments), LogManager::LOG_LEVEL_INFO);
        $this->helper->initEavEntityCollection('catalog_category_entity_varchar');
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance(LogManager::LOG_LEVEL_INFO);
            $sourceDocument = $this->source->getDocument($sourceDocName);
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $destDocument = $this->destination->getDocument($destinationName);
            $this->destination->clearDocument($destinationName);
            $this->logger->debug('migrating', ['table' => $sourceDocName]);
            $recordTransformer = $this->recordTransformerFactory->create(
                [
                    'sourceDocument' => $sourceDocument,
                    'destDocument' => $destDocument,
                    'mapReader' => $this->map
                ]
            );
            $recordTransformer->init();

            $this->progress->start(
                ceil($this->source->getRecordsCount($sourceDocName) / $this->source->getPageSize($sourceDocName)),
                LogManager::LOG_LEVEL_DEBUG
            );

            $pageNumber = 0;
            while (!empty($items = $this->source->getRecords($sourceDocName, $pageNumber))) {
                $pageNumber++;

                $destinationRecords = $destDocument->getRecords();
                foreach ($items as $data) {
                    /** @var Record $record */
                    $record = $this->recordFactory->create(['document' => $sourceDocument, 'data' => $data]);
                    /** @var Record $destRecord */
                    $destRecord = $this->recordFactory->create(['document' => $destDocument]);
                    $recordTransformer->transform($record, $destRecord);
                    $destinationRecords->addRecord($destRecord);
                    $this->helper->updateAttributeData($data);
                }
                $this->source->setLastLoadedRecord($sourceDocName, end($items));
                $this->progress->advance(LogManager::LOG_LEVEL_DEBUG);
                $this->destination->saveRecords($destinationName, $destinationRecords);
            }
            $this->progress->finish(LogManager::LOG_LEVEL_DEBUG);
        }
        $this->helper->saveRecords();
        $this->helper->updateEavAttributes();
        $this->progress->finish(LogManager::LOG_LEVEL_INFO);
        return true;
    }
}
