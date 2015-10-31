<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\Reader\MapFactory;
use Migration\ResourceModel;
use Migration\Reader\MapInterface;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        $mapConfigOption = 'map_file'
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check($this->source->getDocumentList(), MapInterface::TYPE_SOURCE);
        $this->check($this->destination->getDocumentList(), MapInterface::TYPE_DEST);
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
        $sourceDocuments = $this->source->getDocumentList();
        $destDocuments = $this->destination->getDocumentList();
        return count($sourceDocuments) + count($destDocuments);
    }
}
