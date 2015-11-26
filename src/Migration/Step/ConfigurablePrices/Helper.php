<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\ResourceModel\Destination;

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
     * @param Destination $destination
     */
    public function __construct(
        Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
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
     * @return array
     */
    public function getDestinationFields()
    {
        return [
            'store_id' => 'catalog_product_entity_decimal',
            'value' => 'catalog_product_entity_decimal',
            'entity_id' => 'catalog_product_entity_decimal',
            'attribute_id' => 'catalog_product_entity_decimal'
        ];
    }

    /**
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
     * @return array
     */
    public function getDestinationRecordsCount()
    {
        return $this->destinationRecordsCount;
    }
}
