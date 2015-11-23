<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\ResourceModel;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\MapInterface;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @param Helper $helper
     * @param Logger $logger
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     */
    public function __construct(
        Helper $helper,
        Logger $logger,
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination
    ) {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->checkFields($this->helper->getSourceDocumentFields(), MapInterface::TYPE_SOURCE);
        $this->checkFields($this->helper->getDestinationDocumentFields(), MapInterface::TYPE_DEST);
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->helper->getSourceDocumentFields()) + count($this->helper->getDestinationDocumentFields());
    }

    /**
     * @param array $tableFields
     * @param string $sourceType
     * @return void
     */
    protected function checkFields($tableFields, $sourceType)
    {
        foreach ($tableFields as $documentName => $fieldsData) {
            $source     = $this->getResourceModel($sourceType);
            $document   = $source->getDocument($documentName);
            $structure  = array_keys($document->getStructure()->getFields());

            $fieldsDiff = array_diff($fieldsData, $structure);
            if (!empty($fieldsDiff)) {
                $this->missingDocumentFields[$sourceType][$documentName] = $fieldsDiff;
            }
            $this->progress->advance();
        }
    }

    /**
     * @param string $sourceType
     * @return ResourceModel\Destination|ResourceModel\Source
     */
    protected function getResourceModel($sourceType)
    {
        $map = [
            MapInterface::TYPE_SOURCE   => $this->source,
            MapInterface::TYPE_DEST     => $this->destination,
        ];
        return $map[$sourceType];
    }
}
