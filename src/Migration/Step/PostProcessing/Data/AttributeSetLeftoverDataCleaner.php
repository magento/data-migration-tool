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
use \Migration\Step\PostProcessing\Model\AttributeSetLeftoverData as AttributeSetLeftoverDataModel;

/**
 * Class AttributeSetLeftoverDataCleaner
 */
class AttributeSetLeftoverDataCleaner
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
     * @var AttributeSetLeftoverDataModel
     */
    private $attributeSetLeftoverDataModel;

    /**
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param ResourceModel\Destination $destination
     * @param Progress $progress
     * @param AttributeSetLeftoverDataModel $attributeSetLeftoverDataModel
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progressBar,
        ResourceModel\Destination $destination,
        Progress $progress,
        AttributeSetLeftoverDataModel $attributeSetLeftoverDataModel
    ) {
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->progress = $progress;
        $this->attributeSetLeftoverDataModel = $attributeSetLeftoverDataModel;
    }

    /**
     * Records which are still in product entity tables but product attribute no longer exist in attribute set
     */
    public function clean()
    {
        $entityValueIds = $this->attributeSetLeftoverDataModel->getLeftoverIds();
        if (!$entityValueIds) {
            return ;
        }
        foreach ($this->attributeSetLeftoverDataModel->getDocuments() as $document) {
            $this->progressBar->advance(LogManager::LOG_LEVEL_INFO);
            if (isset($entityValueIds[$document]) && $entityValueIds[$document]) {
                $this->destination->deleteRecords(
                    $this->destination->addDocumentPrefix($document),
                    'value_id',
                    $entityValueIds[$document]
                );
            }
        }
    }

    /**
     * Get documents
     *
     * @return array
     */
    public function getDocuments()
    {
        return $this->attributeSetLeftoverDataModel->getDocuments();
    }
}
