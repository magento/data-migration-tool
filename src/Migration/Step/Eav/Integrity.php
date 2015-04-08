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
        $documents = $this->helper->getDocuments();
        $this->progress->start(count($documents));
        foreach ($documents as $sourceDocumentName) {
            if ($sourceDocumentName == 'enterprise_rma_item_eav_attribute'
                && !$this->source->getDocument($sourceDocumentName)
            ) {
                continue;
            }
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
        return count($this->helper->getDocuments());
    }
}
