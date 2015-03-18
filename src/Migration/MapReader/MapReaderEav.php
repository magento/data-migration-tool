<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

use Migration\Config;

/**
 * Class MapReaderEav
 */
class MapReaderEav extends MapReaderAbstract
{
    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
        $this->init($this->config->getOption('eav_map_file'));
    }

    // @codeCoverageIgnoreStart

    /**
     * EAV tables mapping
     *
     * @return array
     */
    public function getDocumentsMap()
    {
        return [
            'eav_attribute_group' => 'eav_attribute_group',
            'eav_attribute_set' => 'eav_attribute_set',
            'eav_attribute' => 'eav_attribute',
            'eav_entity_attribute' => 'eav_entity_attribute',
            'catalog_eav_attribute' => 'catalog_eav_attribute',
            'customer_eav_attribute' => 'customer_eav_attribute',
            'eav_entity_type' => 'eav_entity_type',
            'customer_eav_attribute_website' => 'customer_eav_attribute_website',
            'eav_attribute_label' => 'eav_attribute_label',
            'eav_attribute_option' => 'eav_attribute_option',
            'eav_attribute_option_value' => 'eav_attribute_option_value',
            'eav_entity' => 'eav_entity',
            'eav_entity_datetime' => 'eav_entity_datetime',
            'eav_entity_decimal' => 'eav_entity_decimal',
            'eav_entity_int' => 'eav_entity_int',
            'eav_entity_store' => 'eav_entity_store',
            'eav_entity_text' => 'eav_entity_text',
            'eav_entity_varchar' => 'eav_entity_varchar',
            'eav_form_element' => 'eav_form_element',
            'eav_form_fieldset' => 'eav_form_fieldset',
            'eav_form_fieldset_label' => 'eav_form_fieldset_label',
            'eav_form_type' => 'eav_form_type',
            'eav_form_type_entity' => 'eav_form_type_entity',
            'enterprise_rma_item_eav_attribute' => 'magento_rma_item_eav_attribute',
            'enterprise_rma_item_eav_attribute_website' => 'magento_rma_item_eav_attribute_website'
        ];
    }

    /**
     * List of tables to be copied without data merge
     *
     * @return array
     */
    public function getJustCopyDocuments()
    {
        return [
            'customer_eav_attribute_website',
            'eav_attribute_label',
            'eav_attribute_option',
            'eav_attribute_option_value',
            'eav_entity',
            'eav_entity_datetime',
            'eav_entity_decimal',
            'eav_entity_int',
            'eav_entity_store',
            'eav_entity_text',
            'eav_entity_varchar',
            'eav_form_element',
            'eav_form_fieldset',
            'eav_form_fieldset_label',
            'eav_form_type',
            'eav_form_type_entity',
            'enterprise_rma_item_eav_attribute_website'
        ];
    }

    /**
     * @return array
     */
    public function getBackupedTablesList()
    {
        return [
            'eav_attribute_set',
            'eav_attribute_group',
            'eav_attribute',
            'eav_entity_attribute',
            'catalog_eav_attribute',
            'customer_eav_attribute',
            'eav_entity_type',
            'enterprise_rma_item_eav_attribute'
        ];
    }
    // @codeCoverageIgnoreEnd
}
