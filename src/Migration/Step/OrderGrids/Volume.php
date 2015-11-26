<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\ResourceModel;
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
     * LogLevelProcessor instance
     *
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progressBar;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param Helper $helper
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ProgressBar\LogLevelProcessor $progressBar,
        Helper $helper
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->helper = $helper;
        parent::__construct($logger);
    }

    /**
     * @return bool
     */
    public function perform()
    {
        $this->progressBar->start($this->getIterationsCount());
        foreach ($this->helper->getDocumentList() as $sourceDocumentName => $destinationDocumentName) {
            $this->progressBar->advance();
            $sourceRecordsCount = $this->source->getRecordsCount($sourceDocumentName);
            $destinationRecordsCount = $this->destination->getRecordsCount($destinationDocumentName);
            if ($sourceRecordsCount != $destinationRecordsCount) {
                $this->errors[] = 'Mismatch of entities in the document: ' . $destinationDocumentName;
            }
        }

        $this->progressBar->finish();
        return $this->checkForErrors();
    }


    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->helper->getDocumentList());
    }
}
