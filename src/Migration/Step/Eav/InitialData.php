<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\Eav;

use Migration\Reader\MapFactory;
use Migration\Reader\Map;
use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;

/**
 * Class InitialData
 */
class InitialData
{
    /**
     * [attribute_id => attributeData]
     * @var array
     */
    protected $attributes;

    /**
     * @var array;
     */
    protected $attributeSets;

    /**
     * @var array;
     */
    protected $attributeGroups;

    /**
     * @var array;
     */
    protected $entityTypes;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param MapFactory $mapFactory
     * @param Source $source
     * @param Destination $destination
     * @param Helper $helper
     */
    public function __construct(MapFactory $mapFactory, Source $source, Destination $destination, Helper $helper)
    {
        $this->map = $mapFactory->create('eav_map_file');
        $this->source = $source;
        $this->destination = $destination;
        $this->helper = $helper;
        $this->initAttributeSets();
        $this->initAttributeGroups();
        $this->initAttributes();
        $this->initEntityTypes();
    }

    /**
     * Load all entity types
     *
     * @return void
     */
    protected function initEntityTypes()
    {
        if ($this->entityTypes === null) {
            $this->entityTypes['source'] = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_id']);
            $this->entityTypes['dest'] = $this->helper->getDestinationRecords('eav_entity_type', ['entity_type_id']);
        }
    }

    /**
     * Load all attributes from source and destination
     *
     * @return void
     */
    protected function initAttributes()
    {
        if ($this->attributes === null) {
            $sourceDocument = 'eav_attribute';

            foreach ($this->helper->getSourceRecords($sourceDocument, ['attribute_id']) as $id => $record) {
                $this->attributes['source'][$id] = $record;
            }

            $destinationRecords = $this->helper->getDestinationRecords(
                $sourceDocument,
                ['entity_type_id', 'attribute_code']
            );
            foreach ($destinationRecords as $id => $record) {
                $this->attributes['dest'][$id] = $record;
            }
        }
    }

    /**
     * Load attribute sets data before migration
     *
     * @return void
     */
    protected function initAttributeSets()
    {
        $this->attributeSets['dest'] = $this->helper->getDestinationRecords(
            'eav_attribute_set',
            ['attribute_set_id']
        );
    }

    /**
     * Load attribute group data before migration
     *
     * @return void
     */
    protected function initAttributeGroups()
    {
        $this->attributeGroups['dest'] = $this->helper->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_set_id', 'attribute_group_name']
        );
    }

    /**
     * Get entity types
     *
     * @codeCoverageIgnore
     * @param string $type
     * @return array
     */
    public function getEntityTypes($type)
    {
        return $this->entityTypes[$type];
    }

    /**
     * Get attributes
     *
     * @codeCoverageIgnoreStart
     * @param string $type
     * @return mixed
     */
    public function getAttributes($type)
    {
        return $this->attributes[$type];
    }

    /**
     * Get attribute sets
     *
     * @param string $type
     * @return array
     */
    public function getAttributeSets($type)
    {
        return $this->attributeSets[$type];
    }

    /**
     * Get attribute groups
     *
     * @param string $type
     * @return array
     */
    public function getAttributeGroups($type)
    {
        return $this->attributeGroups[$type];
    }
}
