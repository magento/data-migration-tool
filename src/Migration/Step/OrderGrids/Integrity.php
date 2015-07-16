<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\Resource;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Resource\Source
     */
    protected $source;

    /**
     * @var Resource\Destination
     */
    protected $destination;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        Helper $helper
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        foreach ($this->helper->getDocumentList() as $documentName) {
            $this->progress->advance();
            $documentColumns = $this->helper->getDocumentColumns($documentName);
            $destinationDocumentStructure = array_keys($this->destination->getStructure($documentName)->getFields());
            foreach (array_diff($documentColumns, $destinationDocumentStructure) as $columnDiff) {
                $message = sprintf(
                    '%s table does not contain field: %s',
                    $documentName,
                    $columnDiff
                );
                $this->logger->error($message);
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
        return count($this->helper->getDocumentList());
    }
}
