<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\App\ProgressBar;
use Migration\ResourceModel;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    protected $readerAttributes;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param Helper $helper
     * @param GroupsFactory $groupsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        Helper $helper,
        GroupsFactory $groupsFactory,
        $mapConfigOption = 'customer_map_file'
    ) {
        $this->helper = $helper;
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->readerAttributes = $groupsFactory->create('customer_attribute_groups_file');
        parent::__construct($progress, $logger, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $messages = [];
        $this->progress->start($this->getIterationsCount());
        $srcDocuments = array_keys($this->readerGroups->getGroup('source_documents'));

        $dstDocuments = [];
        foreach ($srcDocuments as $sourceDocumentName) {
            $dstDocuments[] = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
            try {
                $this->helper->getAttributeType($sourceDocumentName);
            } catch (\Migration\Exception $e) {
                $messages[] = $e->getMessage();
            }
            $this->progress->advance();
        }

        $this->check($srcDocuments, MapInterface::TYPE_SOURCE);
        $this->check($dstDocuments, MapInterface::TYPE_DEST);

        $this->progress->finish();
        foreach ($messages as $message) {
            $this->logger->error($message);
        }

        return $this->checkForErrors() && empty($messages);
    }

    /**
     * {@inheritdoc}
     */
    protected function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('source_documents')) * 2;
    }
}
