<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

use Migration\ResourceModel;

/**
 * Class ProductUrlPathCleaner
 */
class ProductUrlPathCleaner
{
    /**
     * @var ResourceModel\Destination
     */
    private $destination;

    /**
     * @var string
     */
    private $productVarcharTypeTable = 'catalog_product_entity_varchar';

    /**
     * @var string
     */
    private $eavAttributesTable = 'eav_attribute';

    /**
     * @var string
     */
    private $eavEntityTypeTable = 'eav_entity_type';

    /**
     * @var string
     */
    private $productEntityTypeCode = 'catalog_product';

    /**
     * @var string
     */
    private $urlPathAttributeCode = 'url_path';

    /**
     * @param ResourceModel\Destination $destination
     */
    public function __construct(
        ResourceModel\Destination $destination
    ) {
        $this->destination = $destination;
    }

    /**
     * Remove records with url_path attributes in product entity table
     *
     * @return void
     */
    public function clean()
    {
        /** @var \Migration\ResourceModel\Adapter\Mysql $adapter */
        $adapter = $this->destination->getAdapter();
        $select = $adapter->getSelect()
            ->from(
                ['ea' => $this->destination->addDocumentPrefix($this->eavAttributesTable)],
                ['attribute_id']

            )->join(
                ['eet' => $this->destination->addDocumentPrefix($this->eavEntityTypeTable)],
                'eet.entity_type_id = ea.entity_type_id',
                []
            )->where(
                'ea.attribute_code = ?',
                $this->urlPathAttributeCode
            )->where(
                'eet.entity_type_code = ?',
                $this->productEntityTypeCode
            );
        $productUrlPathId = $adapter->getSelect()->getAdapter()->fetchOne($select);
        if ($productUrlPathId) {
            /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $adapter */
            $adapter = $this->destination->getAdapter()->getSelect()->getAdapter();
            $adapter->delete(
                $this->destination->addDocumentPrefix($this->productVarcharTypeTable),
                "attribute_id = $productUrlPathId"
            );
        }
    }

    /**
     * Get documents
     *
     * @return array
     */
    public function getDocuments()
    {
        return [
            $this->productVarcharTypeTable
        ];
    }
}
