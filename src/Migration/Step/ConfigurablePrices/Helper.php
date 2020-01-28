<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\ConfigurablePrices;

use Migration\ResourceModel\Destination;
use Migration\ResourceModel\Source;
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
    private $destination;

    /**
     * @var Source
     */
    private $source;

    /**
     * @var array
     */
    private $destinationRecordsCount = 0;

    /**
     * @var string
     */
    private $editionMigrate = '';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Migration\ResourceModel\AdapterInterface
     */
    private $sourceAdapter;

    /**
     * @param Destination $destination
     * @param Source $source
     * @param Config $config
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Destination $destination,
        Source $source,
        Config $config,
        ModuleListInterface $moduleList
    ) {
        $this->source = $source;
        $this->sourceAdapter = $this->source->getAdapter();
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
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            || $this->moduleList->has('Magento_CatalogStaging') === false
            ? 'entity_id'
            : 'row_id';
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


    /**
     * Get configurable price
     *
     * @param array $entityIds
     * @return \Magento\Framework\DB\Select
     */
    public function getConfigurablePrice(array $entityIds = [])
    {
        $entityIdName = $this->editionMigrate == Config::EDITION_MIGRATE_OPENSOURCE_TO_OPENSOURCE
            || $this->moduleList->has('Magento_CatalogStaging') === false
            ? 'entity_id'
            : 'row_id';
        $priceAttributeId = $this->getPriceAttributeId();
        $entityIds = $entityIds ?: new \Zend_Db_Expr(
            'select product_id from ' . $this->source->addDocumentPrefix('catalog_product_super_attribute')
        );
        $priceExpr = new \Zend_Db_Expr(
            'IF(sup_ap.is_percent = 1, TRUNCATE(mt.value + (mt.value * sup_ap.pricing_value/100), 4), ' .
            ' mt.value + SUM(sup_ap.pricing_value))'
        );
        $fields = [
            'value' => $priceExpr,
            'attribute_id' => new \Zend_Db_Expr($priceAttributeId)
        ];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $this->sourceAdapter->getSelect();
        $select->from(['mt' => $this->source->addDocumentPrefix('catalog_product_entity_decimal')], $fields)
            ->joinLeft(
                ['sup_a' => $this->source->addDocumentPrefix('catalog_product_super_attribute')],
                'mt.entity_id = product_id',
                []
            )
            ->joinInner(
                ['sup_ap' => $this->source->addDocumentPrefix('catalog_product_super_attribute_pricing')],
                'sup_ap.product_super_attribute_id = sup_a.product_super_attribute_id',
                []
            )
            ->joinInner(
                ['supl' => $this->source->addDocumentPrefix('catalog_product_super_link')],
                'mt.entity_id = supl.parent_id',
                [$entityIdName => 'product_id']
            )
            ->joinInner(
                ['pint' => $this->source->addDocumentPrefix('catalog_product_entity_int')],
                'pint.entity_id = supl.product_id and pint.attribute_id = sup_a.attribute_id ' .
                ' and pint.value = sup_ap.value_index',
                []
            )
            ->joinInner(
                ['cs' => $this->source->addDocumentPrefix('core_store')],
                'cs.website_id = sup_ap.website_id',
                ['store_id']
            )
            ->where('mt.entity_id in (?)', $entityIds)
            ->where('mt.attribute_id = ?', $priceAttributeId)
            ->group([$entityIdName, 'cs.store_id']);
        ;
        return $select;
    }

    /**
     * Get price attribute id
     *
     * @return string
     */
    protected function getPriceAttributeId()
    {
        $select = $this->sourceAdapter->getSelect();
        $select->from($this->source->addDocumentPrefix('eav_attribute'))->where('attribute_code = ?', 'price');
        return $select->getAdapter()->fetchOne($select);
    }
}
