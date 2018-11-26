<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\App\Step\AbstractVolume;
use Migration\ResourceModel;
use Migration\App\ProgressBar;
use Migration\Logger\Logger;
use Migration\Step\Stores\Model\DocumentsList;

/**
 * Class Volume
 */
class Volume extends AbstractVolume
{
    /**
     * @var ResourceModel\Source
     */
    private $source;

    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var ProgressBar\LogLevelProcessor
     */
    private $progress;

    /**
     * @var DocumentsList
     */
    private $documentsList;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param DocumentsList $documentsList
     * @param Logger $logger
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        DocumentsList $documentsList,
        Logger $logger
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->documentsList = $documentsList;
        parent::__construct($logger);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        foreach ($this->documentsList->getDocumentsMap() as $sourceName => $destinationName) {
            $this->progress->advance();
            $sourceCount = $this->source->getRecordsCount($sourceName);
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
        $this->progress->finish();
        return $this->checkForErrors(Logger::ERROR);
    }

    /**
     * Get iterations count for step
     *
     * @return int
     */
    private function getIterationsCount()
    {
        return count($this->documentsList->getDocumentsMap());
    }
}
