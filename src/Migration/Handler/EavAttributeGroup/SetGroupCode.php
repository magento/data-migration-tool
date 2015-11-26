<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttributeGroup;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;

/**
 * Class SetGroupCode
 */
class SetGroupCode extends \Migration\Handler\AbstractHandler implements \Migration\Handler\HandlerInterface
{
    /**
     * Can start
     *
     * @var bool
     */
    protected $canStart;

    /**
     * @var array
     */
    protected $productAttributeSets;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @param Config $config
     * @param Source $source
     * @throws Exception
     */
    public function __construct(Config $config, Source $source)
    {
        $this->canStart = $config->getSource()['type'] == DatabaseStage::SOURCE_TYPE;
        $this->source = $source;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        if (!$this->canStart) {
            return;
        }
        $this->validate($recordToHandle);
        $productAttributeSets = $this->getProductAttributeSets();
        if (!isset($productAttributeSets[$recordToHandle->getValue('attribute_set_id')])) {
            $recordToHandle->setValue($this->field, null);
            return;
        }

        $newValue = preg_replace('/[^a-z0-9]+/', '-', strtolower($recordToHandle->getValue('attribute_group_name')));
        $newValue = ($newValue == 'migration-general') ? 'product-details' : $newValue;
        $recordToHandle->setValue($this->field, $newValue);
    }

    /**
     * Get attribute set IDs for entity type 'catalog_product'
     * @return array
     */
    protected function getProductAttributeSets()
    {
        if (empty($this->productAttributeSets)) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()
                ->from(
                    ['as' => $this->source->addDocumentPrefix('eav_attribute_set')],
                    ['attribute_set_id']
                )->join(
                    ['et' => $this->source->addDocumentPrefix('eav_entity_type')],
                    'et.entity_type_id = as.entity_type_id',
                    []
                )->where('et.entity_type_code = ?', 'catalog_product');
            $this->productAttributeSets = array_flip($query->getAdapter()->fetchCol($query));
        }
        return $this->productAttributeSets;
    }
}
