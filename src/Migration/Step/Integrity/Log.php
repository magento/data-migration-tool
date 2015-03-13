<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

use Migration\Logger\Logger;
use Migration\MapReader\MapReaderLog;
use Migration\MapReaderInterface;
use Migration\ProgressBar;
use Migration\Resource;

/**
 * Class Eav
 */
class Log extends AbstractIntegrity
{
    /**
     * @var MapReaderLog
     */
    protected $map;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderLog $mapReader
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderLog $mapReader
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapReader);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check(array_keys($this->map->getDocumentList()), MapReaderInterface::TYPE_SOURCE);
        $this->check(array_values($this->map->getDocumentList()), MapReaderInterface::TYPE_DEST);
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
        return count($this->map->getDestDocumentsToClear()) + count($this->map->getDocumentList());
    }
}
