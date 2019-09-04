<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Customer;

use Migration\Logger\Logger;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\Reader\MapInterface;
use Migration\App\ProgressBar;
use Migration\ResourceModel;
use Migration\Step\Customer\Model;
use Migration\Config;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Groups
     */
    private $readerGroups;

    /**
     * @var \Migration\Reader\Groups
     */
    private $readerAttributes;

    /**
     * @var Model\AttributesDataToSkip
     */
    private $attributesDataToSkip;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Config $config
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param Model\AttributesDataToSkip $attributesDataToSkip
     * @param GroupsFactory $groupsFactory
     * @param string $mapConfigOption
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Config $config,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        Model\AttributesDataToSkip $attributesDataToSkip,
        GroupsFactory $groupsFactory,
        $mapConfigOption = 'customer_map_file'
    ) {
        $this->attributesDataToSkip = $attributesDataToSkip;
        $this->readerGroups = $groupsFactory->create('customer_document_groups_file');
        $this->readerAttributes = $groupsFactory->create('customer_attribute_groups_file');
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * @inheritdoc
     */
    public function perform()
    {
        $this->progress->start(0);
        $attributesError = false;
        try {
            $this->attributesDataToSkip->getSkippedAttributes();
        } catch (\Migration\Exception $e) {
            $this->logger->error($e->getMessage());
            $attributesError = true;
        }
        $dstDocuments = [];
        $srcDocuments = array_keys($this->readerGroups->getGroup('source_documents'));
        foreach ($srcDocuments as $sourceDocumentName) {
            $dstDocuments[] = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
        }
        $this->check($srcDocuments, MapInterface::TYPE_SOURCE);
        $this->check($dstDocuments, MapInterface::TYPE_DEST);
        $this->progress->finish();

        return $this->checkForErrors() && $attributesError === false;
    }

    /**
     * @inheritdoc
     */
    protected function getIterationsCount()
    {
        return count($this->readerGroups->getGroup('source_documents')) * 2;
    }
}
