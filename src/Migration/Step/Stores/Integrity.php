<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\ResourceModel;
use Migration\App\ProgressBar;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
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
     * @var ProgressBar\LogLevelProcessor
     */
    protected $progress;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param Helper $helper
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        Helper $helper
    ) {
        $this->progress = $progress;
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $result = true;
        $this->progress->start(count($this->helper->getDocumentList()));
        foreach ($this->helper->getDocumentList() as $sourceName => $destinationName) {
            $this->progress->advance();
            $result &= (bool)$this->source->getDocument($sourceName);
            $result &= (bool)$this->destination->getDocument($destinationName);
        }
        $this->progress->finish();
        return (bool)$result;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIterationsCount()
    {
        return 0;
    }
}
