<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\TierPrice;

use Migration\ResourceModel;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\MapInterface;
use Migration\Reader\MapFactory;
use Migration\Config;

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
     * @param Config $config
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        Helper $helper,
        Logger $logger,
        Config $config,
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        $mapConfigOption = 'tier_price_map_file'
    ) {
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check($this->helper->getSourceDocuments(), MapInterface::TYPE_SOURCE);
        $this->check($this->helper->getDestinationDocuments(), MapInterface::TYPE_DEST);
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
        return count($this->helper->getSourceDocuments()) + count($this->helper->getDestinationDocuments());
    }

    /**
     * @inheritdoc
     */
    protected function getMappedDocumentName($documentName, $type)
    {
        return $this->helper->getMappedDocumentName($documentName, $type);
    }
}
