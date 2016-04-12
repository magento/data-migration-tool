<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\TierPrice;

use Migration\Config;

/**
 * Class Helper
 */
class Helper
{
    const DESTINATION_DOCUMENT_NAME = 'catalog_product_entity_tier_price';

    /**
     * @var string
     */
    protected $editionMigrate = '';

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->editionMigrate = $config->getOption('edition_migrate');
    }

    /**
     * @return string
     */
    public function getDestinationName()
    {
        return self::DESTINATION_DOCUMENT_NAME;
    }

    /**
     * @return array
     */
    public function getSourceDocumentFields()
    {
        return [
            self::DESTINATION_DOCUMENT_NAME => [
                'value_id',
                'entity_id',
                'all_groups',
                'customer_group_id',
                'qty',
                'value',
                'website_id',
            ],
            'catalog_product_entity_group_price' => [
                'value_id',
                'entity_id',
                'all_groups',
                'customer_group_id',
                'value',
                'website_id',
            ],
        ];
    }

    /**
     * @return array
     */
    public function getDestinationDocumentFields()
    {
        $entityIdName = (empty($this->editionMigrate) || $this->editionMigrate == Config::EDITION_MIGRATE_CE_TO_CE) 
            ? 'entity_id' 
            : 'row_id';
        return [
            self::DESTINATION_DOCUMENT_NAME => [
                'value_id',
                $entityIdName,
                'all_groups',
                'customer_group_id',
                'qty',
                'value',
                'website_id',
            ],
        ];
    }
}
