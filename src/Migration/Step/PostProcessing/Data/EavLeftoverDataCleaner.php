<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @var EavLeftoverDataModel
     */
    private $eavLeftoverDataModel;

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
        foreach ($this->eavLeftoverDataModel->getDocuments() as $document) {
            $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
            $this->destination->deleteRecords($document, 'attribute_id', $attributeIds);
        }
    }

    /**
     * Get documents
     *
     * @return array
     */
    public function getDocuments()
    {
        return $this->eavLeftoverDataModel->getDocuments(false);
    }
}
