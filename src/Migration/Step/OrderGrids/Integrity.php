<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\OrderGrids;

use Migration\ResourceModel;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Config;

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
     * @var ResourceModel\Source
     */
    protected $source;

    /**
     * @var ResourceModel\Destination
     */
    protected $destination;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Config $config
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Config $config,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Helper $helper
    ) {
        $this->progress = $progress;
        $this->logger = $logger;
        $this->config = $config;
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $errors = false;
        $this->progress->start(0);
        foreach ($this->helper->getDocumentList() as $documentName) {
            $documentColumns = $this->helper->getDocumentColumns($documentName);
            $destinationDocumentStructure = array_keys($this->destination->getStructure($documentName)->getFields());
            foreach (array_diff($documentColumns, $destinationDocumentStructure) as $columnDiff) {
                $message = sprintf(
                    '%s table does not contain field: %s',
                    $documentName,
                    $columnDiff
                );
                $this->logger->error($message);
                $errors = true;
            }
        }
        if (!$errors) {
            $this->progress->finish();
        }
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
