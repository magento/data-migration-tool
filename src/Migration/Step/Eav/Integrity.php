<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Logger\Logger;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderEav;
use Migration\ProgressBar;
use Migration\Resource;

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
     * @var MapReaderEav
     */
    protected $map;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderEav $mapReader
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderEav $mapReader,
        Helper $helper
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapReader);
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check(array_keys($this->map->getDocumentsMap()), MapReaderInterface::TYPE_SOURCE);
        $this->check(array_values($this->map->getDocumentsMap()), MapReaderInterface::TYPE_DEST);
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
        return count($this->map->getDocumentsMap()) * 2;
    }
}
