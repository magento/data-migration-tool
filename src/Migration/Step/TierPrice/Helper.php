<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Get source documents
     *
     * @return array
     */
    public function getSourceDocuments()
    {
        $map = $this->getDocumentsMap();
        return array_keys($map[MapInterface::TYPE_SOURCE]);
    }

    /**
     * Get destination documents
     *
     * @return array
     */
    public function getDestinationDocuments()
    {
        $map = $this->getDocumentsMap();
        return array_keys($map[MapInterface::TYPE_DEST]);
    }

    /**
     * Get mapped document name
     *
     * @param string $documentName
     * @param string $type
     * @return mixed
     */
    public function getMappedDocumentName($documentName, $type)
    {
        $map = $this->getDocumentsMap();
        return $map[$type][$documentName];
    }

    /**
     * Get documents map
     *
     * @return array
     */
    protected function getDocumentsMap()
    {
        $sourceDocuments = ['catalog_product_entity_tier_price' => 'catalog_product_entity_tier_price'];
        if (!empty($this->editionNumber) && !in_array($this->editionNumber, $this->notExistsGroupPriceTable)) {
            $sourceDocuments['catalog_product_entity_group_price'] = 'catalog_product_entity_tier_price';
        }
        return [
            MapInterface::TYPE_SOURCE => $sourceDocuments,
            MapInterface::TYPE_DEST => ['catalog_product_entity_tier_price' => 'catalog_product_entity_tier_price'],
        ];
    }
}
