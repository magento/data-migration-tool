<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\ResourceModel;
use Migration\App\ProgressBar;

/**
 * Class Volume
 */
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
     * @inheritdoc
     */
    public function perform()
    {
        $this->progressBar->start($this->getIterationsCount());
        foreach ($this->helper->getDocumentList() as $sourceName => $destinationName) {
            $this->progressBar->advance();
            $sourceCount = $this->source->getRecordsCount($sourceName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
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
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->helper->getDocumentList());
    }
}
