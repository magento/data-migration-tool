<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Logger\Logger;
use Migration\MapReaderInterface;
use Migration\MapReader\MapReaderEav;
use Migration\App\ProgressBar;
use Migration\Resource;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var MapReaderEav
     */
    protected $map;

    /**
     * @var \Migration\ListsReader
     */
    protected $readerSimple;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReaderEav $mapReader
     * @param \Migration\ListsReaderFactory $listsReaderFactory
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReaderEav $mapReader,
        \Migration\ListsReaderFactory $listsReaderFactory
    ) {
        parent::__construct($progress, $logger, $source, $destination, $mapReader);
        $this->readerSimple = $listsReaderFactory->create(['optionName' => 'eav_list_file']);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());

        $documents = $this->readerSimple->getList('documents');
        foreach ($documents as $sourceDocumentName) {
            $this->check([$sourceDocumentName], MapReaderInterface::TYPE_SOURCE);
            $destinationDocumentName = $this->map->getDocumentMap($sourceDocumentName, MapReaderInterface::TYPE_SOURCE);
            $this->check([$destinationDocumentName], MapReaderInterface::TYPE_DEST);
        }

        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Returns number of iterations for integrity check
     * @return mixed
     */
    protected function getIterationsCount()
    {
        return count($this->readerSimple->getList('documents')) * 2;
    }
}
