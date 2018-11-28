<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Log;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\Map;
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
     * @var Map
     */
    protected $map;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param ProgressBar\LogLevelProcessor $progress
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        ProgressBar\LogLevelProcessor $progress,
        GroupsFactory $groupsFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->map = $mapFactory->create('log_map_file');
        $this->progress = $progress;
        $this->readerGroups = $groupsFactory->create('log_document_groups_file');
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        $this->progress->start($this->getIterationsCount());
        foreach ($sourceDocuments as $sourceDocName) {
            $this->progress->advance();
            $destinationName = $this->map->getDocumentMap($sourceDocName, MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $sourceCount = $this->source->getRecordsCount($sourceDocName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $this->errors[] = sprintf(
                    'Mismatch of entities in the document: %s Source: %s Destination: %s',
                    $destinationName,
                    $sourceCount,
                    $destinationCount
                );
            }
        }
        if (!$this->checkCleared(array_keys($this->readerGroups->getGroup('destination_documents_to_clear')))) {
            $this->errors[] = 'Log documents in the destination resource are not cleared';
        }
        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Check cleared
     *
     * @param array $documents
     * @return bool
     */
    protected function checkCleared($documents)
    {
        $documentsAreEmpty = true;
        foreach ($documents as $documentName) {
            $this->progress->advance();
            $destinationCount = $this->destination->getRecordsCount($documentName);
            if ($destinationCount > 0) {
                $documentsAreEmpty = false;
                break;
            }
            $destinationCount = null;
        }
        return $documentsAreEmpty;
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    protected function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('destination_documents_to_clear'))
            + count($this->readerGroups->getGroup('source_documents'));
    }
}
