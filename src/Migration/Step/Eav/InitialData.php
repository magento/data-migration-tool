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
    private $attributes;

    /**
     * @var array;
     */
    private $attributeSets;

    /**
     * @var array;
     */
    private $attributeGroups;

    /**
     * @var array;
     */
    private $entityTypes;

    /**
     * @var array;
     */
    private $entityAttributes;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var Destination
     */
    private $destination;

    /**
     * @var Map
     */
    private $map;

    /**
     * @var Helper
     */
    private $helper;

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
        $this->initEntityAttribute();
    }

    /**
     * Load all entity types
     *
     * @return void
     */
    private function initEntityTypes()
    {
        if ($this->entityTypes) {
            return;
        }
        $this->entityTypes['source'] = $this->helper->getSourceRecords('eav_entity_type', ['entity_type_id']);
        $this->entityTypes['dest'] = $this->helper->getDestinationRecords('eav_entity_type', ['entity_type_id']);
    }

    /**
     * Load all attributes from source and destination
     *
     * @return void
     */
    private function initAttributes()
    {
        if ($this->attributes) {
            return;
        }
        $sourceDocument = 'eav_attribute';
        foreach ($this->helper->getSourceRecords($sourceDocument, ['attribute_id']) as $id => $record) {
            $this->attributes['source'][$id] = $record;
        }
        foreach ($this->helper->getDestinationRecords($sourceDocument, ['attribute_id']) as $id => $record) {
            $this->attributes['dest'][$id] = $record;
        }
    }

    /**
     * Load attribute sets data before migration
     *
     * @return void
     */
    private function initAttributeSets()
    {
        if ($this->attributeSets) {
            return;
        }
        $this->attributeSets['source'] = $this->helper->getSourceRecords(
            'eav_attribute_set',
            ['attribute_set_id']
        );
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
    private function initAttributeGroups()
    {
        if ($this->attributeGroups) {
            return;
        }
        $this->attributeGroups['source'] = $this->helper->getSourceRecords(
            'eav_attribute_group',
            ['attribute_group_id']
        );
        $this->attributeGroups['dest'] = $this->helper->getDestinationRecords(
            'eav_attribute_group',
            ['attribute_group_id']
        );
    }
    
    /**
     * Load entity attribute data before migration
     *
     * @return void
     */
    private function initEntityAttribute()
    {
        if ($this->entityAttributes) {
            return;
        }
        $this->entityAttributes['source'] = $this->helper->getSourceRecords(
            'eav_entity_attribute',
            ['entity_attribute_id']
        );
        $this->entityAttributes['dest'] = $this->helper->getDestinationRecords(
            'eav_entity_attribute',
            ['entity_attribute_id']
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

    /**
     * Get Eav entity attributes
     *
     * @param string $type
     * @return array
     */
    public function getEntityAttributes($type)
    {
        return $this->entityAttributes[$type];
    }
}
