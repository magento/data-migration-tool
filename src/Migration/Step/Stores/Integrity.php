<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Stores;

use Migration\ResourceModel;
use Migration\Logger\Logger;
use Migration\App\ProgressBar;
use Migration\Reader\MapInterface;
use Migration\Reader\MapFactory;
use Migration\Step\Stores\Model\DocumentsList;
use Migration\Config;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var DocumentsList
     */
    protected $documentsList;

    /**
     * @param DocumentsList $documentsList
     * @param Logger $logger
     * @param Config $config
     * @param ProgressBar\LogLevelProcessor $progress
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        DocumentsList $documentsList,
        Logger $logger,
        Config $config,
        ProgressBar\LogLevelProcessor $progress,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        $mapConfigOption = 'stores_map_file'
    ) {
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
        $this->documentsList = $documentsList;
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());
        $this->check($this->documentsList->getSourceDocuments(), MapInterface::TYPE_SOURCE);
        $this->check($this->documentsList->getDestinationDocuments(), MapInterface::TYPE_DEST);
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
        return count($this->documentsList->getSourceDocuments())
            + count($this->documentsList->getDestinationDocuments());
    }
}
