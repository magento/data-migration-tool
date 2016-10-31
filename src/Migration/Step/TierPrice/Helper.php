<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Step\TierPrice;

use Migration\Config;
use Migration\Reader\MapInterface;

/**
 * Class Helper
 */
class Helper
{
    /**
     * @var string
     */
    protected $editionNumber;

    /**
     * @var array
     */
    protected $notExistsGroupPriceTable = [
        '1.11.0.0',
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
        $this->editionNumber = $config->getOption('edition_number');
    }

    /**
     * @return string
     */
    public function getDestinationName()
    {
        return 'catalog_product_entity_tier_price';
    }

    public function getSourceDocuments()
    {
        return array_keys($this->getDocumentList());
    }

    public function getDestinationDocuments()
    {
        $documentList = $this->getDocumentList();
        return [reset($documentList)];
    }

    public function getDocumentList()
    {
        $sourceDocuments = ['catalog_product_entity_tier_price' => 'catalog_product_entity_tier_price'];
        if (!empty($this->editionNumber) && !in_array($this->editionNumber, $this->notExistsGroupPriceTable)) {
            $sourceDocuments['catalog_product_entity_group_price'] = 'catalog_product_entity_tier_price';
        }
        return $sourceDocuments;
    }

    public function getDocumentsMap()
    {
        return [
            MapInterface::TYPE_SOURCE => $this->getDocumentList(),
            MapInterface::TYPE_DEST => ['catalog_product_entity_tier_price' => 'catalog_product_entity_tier_price'],
        ];
    }
}
