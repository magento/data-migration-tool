<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @var string
     */
    protected $editionNumber = '';

    /**
     * @var array
     */
    protected $notExistsGroupPriceTable = [
        '1.11.0.0',
        '1.11.0.1',
        '1.11.0.1',
        '1.11.0.2',
        '1.11.1.0',
        '1.11.2.0',
        '1.6.0.0',
        '1.6.1.0',
        '1.6.2.0'
    ];

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->editionMigrate = $config->getOption('edition_migrate');
        $this->editionNumber = $config->getOption('edition_number');
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
        $sourceDocumentFields = [
            self::DESTINATION_DOCUMENT_NAME => [
                'value_id',
                'entity_id',
                'all_groups',
                'customer_group_id',
                'qty',
                'value',
                'website_id',
            ],
        ];
        if (!empty($this->editionNumber) && !in_array($this->editionNumber, $this->notExistsGroupPriceTable)) {
            $sourceDocumentFields['catalog_product_entity_group_price'] = [
                'value_id',
                'entity_id',
                'all_groups',
                'customer_group_id',
                'value',
                'website_id',
            ];
        }
        return $sourceDocumentFields;
    }

    /**
     * @return array
     */
    public function getDestinationDocumentFields()
    {
        $entityIdName = $this->getEntityIdNameMap()['destination'];
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

    /**
     * @return string
     */
    public function getEntityIdNameMap()
    {
        $entityIdName = (empty($this->editionMigrate) || $this->editionMigrate == Config::EDITION_MIGRATE_CE_TO_CE)
            ? 'entity_id'
            : 'row_id';

        return ['source' => 'entity_id', 'destination' => $entityIdName];
    }
}
