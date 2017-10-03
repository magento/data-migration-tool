<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Logger\Logger;
use Migration\Reader\MapInterface;
use Migration\Reader\GroupsFactory;
use Migration\Reader\MapFactory;
use Migration\App\ProgressBar;
use Migration\ResourceModel;
use Migration\Step\Eav\Integrity\AttributeGroupNames as AttributeGroupNamesIntegrity;
use Migration\Step\Eav\Integrity\AttributeFrontendInput as AttributeFrontendInputIntegrity;
use Migration\Config;

/**
 * Class Integrity
 */
class Integrity extends \Migration\App\Step\AbstractIntegrity
{
    /**
     * @var \Migration\Reader\Groups
     */
    private $groups;

    /**
     * @var AttributeGroupNamesIntegrity
     */
    private $attributeGroupNamesIntegrity;

    /**
     * @var AttributeFrontendInputIntegrity
     */
    private $attributeFrontendInputIntegrity;

    /**
     * @param ProgressBar\LogLevelProcessor $progress
     * @param Logger $logger
     * @param Config $config
     * @param ResourceModel\Source $source
     * @param ResourceModel\Destination $destination
     * @param MapFactory $mapFactory
     * @param GroupsFactory $groupsFactory
     * @param AttributeGroupNamesIntegrity $attributeGroupNamesIntegrity
     * @param AttributeFrontendInputIntegrity $attributeFrontendInputIntegrity
     * @param string $mapConfigOption
     *
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        ProgressBar\LogLevelProcessor $progress,
        Logger $logger,
        Config $config,
        ResourceModel\Source $source,
        ResourceModel\Destination $destination,
        MapFactory $mapFactory,
        GroupsFactory $groupsFactory,
        AttributeGroupNamesIntegrity $attributeGroupNamesIntegrity,
        AttributeFrontendInputIntegrity $attributeFrontendInputIntegrity,
        $mapConfigOption = 'eav_map_file'
    ) {
        $this->groups = $groupsFactory->create('eav_document_groups_file');
        $this->attributeGroupNamesIntegrity = $attributeGroupNamesIntegrity;
        $this->attributeFrontendInputIntegrity = $attributeFrontendInputIntegrity;
        parent::__construct($progress, $logger, $config, $source, $destination, $mapFactory, $mapConfigOption);
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->progress->start($this->getIterationsCount());

        $documents = array_keys($this->groups->getGroup('documents'));
        foreach ($documents as $sourceDocumentName) {
            $this->check([$sourceDocumentName], MapInterface::TYPE_SOURCE);
            $destinationDocumentName = $this->map->getDocumentMap($sourceDocumentName, MapInterface::TYPE_SOURCE);
            $this->check([$destinationDocumentName], MapInterface::TYPE_DEST);
        }

        $this->incompatibleDocumentFieldsData[MapInterface::TYPE_SOURCE] = array_merge(
            $this->attributeGroupNamesIntegrity->checkAttributeGroupNames(),
            $this->attributeFrontendInputIntegrity->checkAttributeFrontendInput()
        );

        $this->progress->finish();
        return $this->checkForErrors();
    }

    /**
     * Returns number of iterations for integrity check
     * @return mixed
     */
    protected function getIterationsCount()
    {
        return count($this->groups->getGroup('documents')) * 2;
    }
}
