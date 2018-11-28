<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\VisualMerchandiser;

use Migration\App\Step\AbstractVolume;
use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
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
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param ProgressBar\LogLevelProcessor $progressBar
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     */
    public function __construct(
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        ProgressBar\LogLevelProcessor $progressBar,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory
    ) {
        $this->source = $source;
        $this->destination = $destination;
        $this->progressBar = $progressBar;
        $this->map = $mapFactory->create('visual_merchandiser_map');
        $this->groups = $groupsFactory->create('visual_merchandiser_document_groups');
        $this->logger = $logger;
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $sourceDocuments = array_keys($this->groups->getGroup('source_documents'));
        $this->progressBar->start(count($sourceDocuments));
        foreach ($sourceDocuments as $sourceName) {
            $this->progressBar->advance();
            $destinationName = $this->map->getDocumentMap($sourceName, \Migration\Reader\MapInterface::TYPE_SOURCE);
            if (!$destinationName) {
                continue;
            }
            $sourceCount = $this->source->getRecordsCount($sourceName);
            $destinationCount = $this->destination->getRecordsCount($destinationName);
            if ($sourceCount != $destinationCount) {
                $this->errors[] = 'Mismatch of entities in the document: ' . $destinationName;
            }
        }
        $this->progressBar->finish();
        return $this->checkForErrors();
    }
}
