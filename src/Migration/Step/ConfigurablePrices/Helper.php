<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\ResourceModel\Destination;
use Migration\Config;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var Destination
     */
    protected $destination;

    /**
     * @var array
     */
    protected $destinationRecordsCount = 0;

    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @var ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param Destination $destination
     * @param Config $config
     */
    public function __construct(
        Destination $destination,
        Config $config,
        ModuleListInterface $moduleList
    ) {
        $this->destination = $destination;
        $this->editionMigrate = $config->getOption('edition_migrate');
        $this->moduleList = $moduleList;
    }

    /**
     * Get source fields
     *
     * @return array
     */
    public function getSourceFields()
    {
        return [
            'website_id' => 'catalog_product_super_attribute_pricing',
            'pricing_value' => 'catalog_product_super_attribute_pricing',
            'product_id' => 'catalog_product_super_link',
            'attribute_id' => 'catalog_product_super_attribute'
        ];
    }

    /**
     * Get destination fields
     *
     * @return array
     */
    public function getDestinationFields()
    {
        $entityIdName = $this->editionMigrate !== Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            && $this->moduleList->has('Magento_CatalogStaging') === true
            ? 'row_id'
            : 'entity_id';

        return [
            'store_id' => 'catalog_product_entity_decimal',
            'value' => 'catalog_product_entity_decimal',
            $entityIdName => 'catalog_product_entity_decimal',
            'attribute_id' => 'catalog_product_entity_decimal'
        ];
    }

    /**
     * Get document list
     *
     * @return array
     */
    public function getDocumentList()
    {
        return [
            'source' => 'catalog_product_super_attribute_pricing',
            'destination' => 'catalog_product_entity_decimal'
        ];
    }

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        if (!$this->getDestinationRecordsCount()) {
            $this->destinationRecordsCount = $this->destination->getRecordsCount(
                $this->getDocumentList()['destination']
            );
        }
    }

    /**
     * Get destination records count
     *
     * @return array
     */
    public function getDestinationRecordsCount()
    {
        return $this->destinationRecordsCount;
    }
}
