<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\Logger\Logger;
use Migration\Reader\ListsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\ProgressBar;
use Migration\Resource;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Lists
     */
    protected $readerList;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapFactory $mapFactory
     * @param ListsFactory $listsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapFactory $mapFactory,
        ListsFactory $listsFactory,
        $mapConfigOption = 'log_map_file'
    ) {
        $this->readerList = $listsFactory->create('log_list_file');
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $srcDocuments = $this->readerList->getList('source_documents');

        $dstDocuments = [];
        foreach ($srcDocuments as $sourceDocumentName) {
            $dstDocuments[] = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
        }

        $this->check($srcDocuments, MapInterface::TYPE_SOURCE);
        $this->check($dstDocuments, MapInterface::TYPE_DEST);

        $dstDocumentList = array_flip($this->destination->getDocumentList());
        foreach ($this->readerList->getList('destination_documents_to_clear') as $document) {
            $this->progress->advance();
            if (!isset($dstDocumentList[$document])) {
                $this->missingDocuments[MapInterface::TYPE_DEST][$document] = true;
            }
        }

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
        return count($this->readerList->getList('destination_documents_to_clear'))
            + count($this->readerList->getList('source_documents')) * 2;
    }
}
