<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
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
        Helper $helper,
        Logger $logger
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
        $documents = $this->helper->getDocumentList();
        $this->progressBar->start(1);
        $oldDestinationRecordsCount = $this->helper->getDestinationRecordsCount();
        $newDestinationRecordsCount = $this->destination->getRecordsCount($documents[MapInterface::TYPE_DEST])
            - $oldDestinationRecordsCount;
        if ($newDestinationRecordsCount != 0) {
            $this->errors[] = 'Mismatch of entities in the document: ' . $documents[MapInterface::TYPE_DEST];
        }
        $this->progressBar->finish();
        return $this->checkForErrors(Logger::ERROR);
    }
}
