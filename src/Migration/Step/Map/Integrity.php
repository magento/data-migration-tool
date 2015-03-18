<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Map;

use Migration\MapReader\MapReaderMain;
use Migration\Resource;
use Migration\MapReaderInterface;
use Migration\Logger\Logger;
use Migration\ProgressBar;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderMain $mapReader
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderMain $mapReader
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapReader);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check($this->source->getDocumentList(), MapReaderInterface::TYPE_SOURCE);
        $this->check($this->destination->getDocumentList(), MapReaderInterface::TYPE_DEST);
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
