<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\App\Progress;
use Migration\Logger\Manager as LogManager;
use \Migration\Step\PostProcessing\Model\EavLeftoverData as EavLeftoverDataModel;

/**
 * Class EavLeftoverDataCleaner
 */
class EavLeftoverDataCleaner
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progressBar;

    /**
     * @var Progress
     */
    private $progress;

    /**
     * @var array
     */
    private $deletedDocumentRowsCount;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Destination $destination
     * @param Progress $progress
     * @param EavLeftoverDataModel $eavLeftoverDataModel
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Destination $destination,
        Progress $progress,
        EavLeftoverDataModel $eavLeftoverDataModel
    ) {
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->progress = $progress;
        $this->eavLeftoverDataModel = $eavLeftoverDataModel;
    }

    /**
     * Deletes records from tables which refer to non existing attributes
     *
     * @return void
     */
    public function clean()
    {
        $attributeIds = $this->eavLeftoverDataModel->getLeftoverAttributeIds();
        if (!$attributeIds) {
            return ;
        }
        foreach ($this->eavLeftoverDataModel->getDocumentsToCheck() as $document) {
            $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
            $rowsCountBefore = $this->destination->getRecordsCount($document);
            $this->destination->deleteRecords($document, 'attribute_id', $attributeIds);
            $deletedCount = $rowsCountBefore - $this->destination->getRecordsCount($document);
            if (!empty($deletedCount)) {
                $this->deletedDocumentRowsCount[$document] = $deletedCount;
            }
        }
        $this->progress->saveProcessedEntities(
            'PostProcessing',
            'deletedDocumentRowsCount',
            $this->deletedDocumentRowsCount
        );
    }

    /**
     * @return int
     */
    public function getIterationsCount()
    {
        return count($this->eavLeftoverDataModel->getDocumentsToCheck());
    }
}
