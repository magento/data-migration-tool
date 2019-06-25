<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

use Migration\ResourceModel;
use Migration\App\Progress;

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
     * @var array
     */
    private $documentRecordsCount;

    /**
     * @var Progress
     */
    private $progress;

    /**
     * @param ResourceModel\Destination $destination
     * @param Progress $progress
     */
    public function __construct(
        ResourceModel\Destination $destination,
        Progress $progress
    ) {
        $this->destination = $destination;
        $this->progress = $progress;
    }

    /**
     * Count records in given documents
     *
     * @param array $documents
     */
    public function count($documents)
    {
        $documents = array_unique($documents);
        foreach ($documents as $document) {
            $recordsCount = $this->destination->getRecordsCount($document);
            $this->documentRecordsCount[$document] = $recordsCount;
        }
    }

    /**
     * Compare current amount of records in given documents and save number of deleted records
     *
     * @param array $documents
     */
    public function saveDeleted($documents)
    {
        $documentsToSave = [];
        $documents = array_unique($documents);
        foreach ($documents as $document) {
            $recordsCount = $this->destination->getRecordsCount($document);
            if (isset($this->documentRecordsCount[$document])
                && $this->documentRecordsCount[$document] > $recordsCount
            ) {
                $documentsToSave[$document] = $this->documentRecordsCount[$document] - $recordsCount;
            }
        }
        $this->progress->saveProcessedEntities(
            'PostProcessing',
            'deletedDocumentRowsCount',
            $documentsToSave
        );
    }
}
