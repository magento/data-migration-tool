<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

use Migration\ResourceModel;
use Migration\App\Progress;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;

/**
 * Class DeletedRecordsCounter
 */
class DeletedRecordsCounter
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var array
     */
    private $documentRecordsCount;

    /**
     * @var Progress
     */
    private $progress;

    /**
     * @var array
     */
    private $deltaDocuments;

    /**
     * @var string
     */
    private $mapConfigOption = 'map_file';

    /**
     * @var string
     */
    private $groupName = 'delta_map';

    /**
     * @var MapInterface
     */
    private $mapReader;

    /**
     * @param ResourceModel\Destination $destination
     * @param ResourceModel\Source $source
     * @param Progress $progress
     * @param GroupsFactory $groupsFactory
     * @param MapFactory $mapFactory
     */
    public function __construct(
        ResourceModel\Destination $destination,
        ResourceModel\Source $source,
        Progress $progress,
        GroupsFactory $groupsFactory,
        MapFactory $mapFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progress = $progress;
        $this->mapReader = $mapFactory->create($this->mapConfigOption);
        $deltaDocumentsSource = $groupsFactory->create('delta_document_groups_file')->getGroup($this->groupName);
        foreach (array_keys($deltaDocumentsSource) as $deltaDocument) {
            if ($deltaDocumentMap = $this->mapReader->getDocumentMap($deltaDocument, MapInterface::TYPE_SOURCE)) {
                $this->deltaDocuments[] = $deltaDocumentMap;
            }
        }
    }

    /**
     * Compare current amount of records in given documents and save number of deleted records
     *
     * @param array $documents
     */
    public function saveChanged($documents)
    {
        $documentsToSave = [];
        $documents = array_unique(array_merge($this->deltaDocuments, $documents));
        foreach ($documents as $document) {
            $recordsCountSource = $this->source->getRecordsCount(
                $this->mapReader->getDocumentMap($document, MapInterface::TYPE_DEST)
            );
            $recordsCountDestination = $this->destination->getRecordsCount($document);
            if ($recordsCountSource != $recordsCountDestination) {
                $documentsToSave[$document] = $recordsCountSource - $recordsCountDestination;
            }
        }
        $this->progress->saveProcessedEntities(
            'PostProcessing',
            'changedDocumentRowsCount',
            $documentsToSave
        );
    }
}
