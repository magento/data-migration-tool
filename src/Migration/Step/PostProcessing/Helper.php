<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\PostProcessing;

use Migration\Config;
use Migration\ResourceModel\Destination;
use Migration\Reader\Map;
use Migration\Reader\MapFactory;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var Map
     */
    protected $map;

    /**
     * @param Config $config
     * @param Destination $destination
     * @param MapFactory $mapFactory
     */
    public function __construct(
        Config $config,
        Destination $destination,
        MapFactory $mapFactory
    ) {
        $this->editionMigrate = $config->getOption('edition_migrate');
        $this->destination = $destination;
        $this->map = $mapFactory->create('map_file');
    }

    /**
     * @return array
     */
    public function getProductDestinationDocumentFields()
    {
        $entityIdName = (empty($this->editionMigrate) || $this->editionMigrate == Config::EDITION_MIGRATE_CE_TO_CE)
            ? 'entity_id'
            : 'row_id';
        return [
            $this->getDestinationDocumentName('catalog_product_entity_datetime') => [
                'value_id',
                'attribute_id',
                'store_id',
                $entityIdName,
                'value',
            ],
            $this->getDestinationDocumentName('catalog_product_entity_decimal') => [
                'value_id',
                'attribute_id',
                'store_id',
                $entityIdName,
                'value',
            ],
            $this->getDestinationDocumentName('catalog_product_entity_int') => [
                'value_id',
                'attribute_id',
                'store_id',
                $entityIdName,
                'value',
            ],
            $this->getDestinationDocumentName('catalog_product_entity_text') => [
                'value_id',
                'attribute_id',
                'store_id',
                $entityIdName,
                'value',
            ],
            $this->getDestinationDocumentName('catalog_product_entity_varchar') => [
                'value_id',
                'attribute_id',
                'store_id',
                $entityIdName,
                'value',
            ],
        ];
    }

    /**
     * @return string
     */
    public function getEavAttributeDocument()
    {
        return $this->getDestinationDocumentName('eav_attribute');
    }

    /**
     * @param string $document
     * @return string
     */
    public function getDestinationDocumentName($document)
    {
        return $this->destination->addDocumentPrefix($document);
    }
}
