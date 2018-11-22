<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Step\PostProcessing\Data;

/**
 * EavLeftoverDataTest class test
 * @dbFixture post_processing
 */
class EavLeftoverDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testGetLeftoverAttributeIds()
    {
        $documentsToCheck = [
            'catalog_category_entity_datetime',
            'catalog_category_entity_decimal',
            'catalog_category_entity_int',
            'catalog_category_entity_text',
            'catalog_category_entity_varchar',
            'catalog_eav_attribute',
            'catalog_product_entity_datetime',
            'catalog_product_entity_decimal',
            'catalog_product_entity_gallery',
            'catalog_product_entity_int',
            'catalog_product_entity_media_gallery',
            'catalog_product_entity_text',
            'catalog_product_entity_varchar',
            'customer_address_entity_datetime',
            'customer_address_entity_decimal',
            'customer_address_entity_int',
            'customer_address_entity_text',
            'customer_address_entity_varchar',
            'customer_eav_attribute',
            'customer_eav_attribute_website',
            'customer_entity_datetime',
            'customer_entity_decimal',
            'customer_entity_int',
            'customer_entity_text',
            'customer_entity_varchar',
            'customer_form_attribute',
            'eav_attribute_label',
            'eav_attribute_option',
            'eav_entity_attribute',
            'eav_form_element',
            'salesrule_product_attribute',
            'weee_tax'
        ];
        $helper = \Migration\TestFramework\Helper::getInstance();
        $objectManager = $helper->getObjectManager();
        $objectManager->get(\Migration\Config::class)
            ->init(dirname(__DIR__) . '/../../_files/' . $helper->getFixturePrefix() . 'config.xml');
        $destination = $objectManager->create(\Migration\ResourceModel\Destination::class);
        $groupsFactory = $objectManager->create(\Migration\Reader\GroupsFactory::class);
        $config = $objectManager->get(\Migration\Config::class);
        /** @var \Migration\Step\PostProcessing\Model\EavLeftoverData $eavLeftoverDataModel */
        $eavLeftoverDataModel = $objectManager->create(
            \Migration\Step\PostProcessing\Model\EavLeftoverData::class,
            [
                'destination' => $destination,
                'groupsFactory' => $groupsFactory,
                'config' => $config
            ]
        );
        $this->assertEquals([1111], $eavLeftoverDataModel->getLeftoverAttributeIds());
        $this->assertEquals($documentsToCheck, $eavLeftoverDataModel->getDocumentsToCheck());
    }
}
