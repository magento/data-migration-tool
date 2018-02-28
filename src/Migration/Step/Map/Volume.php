<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\App\Progress;
use Migration\App\ProgressBar;

class Volume extends AbstractVolume
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
     * @var Map
     */
    protected $map;

    /**
     * LogLevelProcessor instance
     *
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progressBar;

    /**
     *
     * @var Progress
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var array
     */
    protected $deletedDocumentRowsCount;

    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param Helper $helper
     * @param Progress $progress
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        ProgressBar\LogLevelProcessor $progressBar,
        Helper $helper,
        Progress $progress
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('map_file');
        $this->progressBar = $progressBar;
        $this->helper = $helper;
        $this->progress = $progress;
        parent::__construct($logger);
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $sourceDocuments = $this->source->getDocumentList();
        $this->progressBar->start(count($sourceDocuments));
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progressBar->advance();
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName
                || !$this->destination->getDocument($destinationName)
                || $this->helper->getFieldsUpdateOnDuplicate($destinationName)
            ) {
                continue;
            }
            $sourceCount = $this->source->getRecordsCount($sourceDocName, true);
            $destinationCount = $this->getDestinationRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $this->errors[] = sprintf(
                    'Mismatch of entities in the document: %s Source: %s Destination: %s',
                    $destinationName,
                    $sourceCount,
                    $destinationCount
                );
            }
        }
        $this->progressBar->finish();
        return $this->checkForErrors();
    }

    /**
     * Get count of records in destination table
     *
     * @param string $destinationName
     * @return int
     */
    public function getDestinationRecordsCount($destinationName)
    {
        if (null === $this->deletedDocumentRowsCount) {
            $this->deletedDocumentRowsCount = $this->progress->getProcessedEntities(
                'PostProcessing',
                'deletedDocumentRowsCount'
            );
        }

        $destinationCount = $this->destination->getRecordsCount($destinationName);
        if (!empty($this->deletedDocumentRowsCount[$destinationName])) {
            $destinationCount -= $this->deletedDocumentRowsCount[$destinationName];
        }
        return $destinationCount;
    }
}
