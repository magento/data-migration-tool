<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Integrity;

use Migration\Logger\Logger;
use Migration\MapReader;
use Migration\ProgressBar;
use Migration\Resource;
use Migration\Step\Log\Helper;
/**
 * Class Eav
 */
class Log extends AbstractIntegrity
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar $progress
     * @param Logger $logger
     * @param Resource\Source $source
     * @param Resource\Destination $destination
     * @param MapReader $mapReader
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar $progress,
        Logger $logger,
        Resource\Source $source,
        Resource\Destination $destination,
        MapReader $mapReader,
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
        $this->check(array_keys($this->helper->getDocumentList()), MapReader::TYPE_SOURCE);
        $this->check(array_values($this->helper->getDocumentList()), MapReader::TYPE_DEST);
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
        return count($this->helper->getDestDocumentsToClear()) + count($this->helper->getDocumentList());
    }
}
