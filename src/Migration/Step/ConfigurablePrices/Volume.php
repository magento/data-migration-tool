<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
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
        $this->progressBar->start(1);
        $this->progressBar->finish();
        return $this->checkForErrors(Logger::ERROR);
    }
}
