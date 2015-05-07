<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttributeGroup;

use Migration\Resource\Record;
use Migration\Resource\Adapter\Mysql;
use Migration\Resource\Source;
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
     * @param Config $config
     * @param Source $source
     * @throws Exception
     */
    public function __construct(Config $config, Source $source)
    {
        $this->canStart = $config->getSource()['type'] == DatabaseStage::SOURCE_TYPE;
        if ($this->canStart) {
            /** @var Mysql $adapter */
            $adapter = $source->getAdapter();
            $query = $adapter->getSelect()
                ->from(
                    ['as' => $source->addDocumentPrefix('eav_attribute_set')],
                    ['attribute_set_id']
                )->join(
                    ['et' => $source->addDocumentPrefix('eav_entity_type')],
                    'et.entity_type_id = as.entity_type_id',
                    []
                )->where('et.entity_type_code = ?', 'catalog_product');
            $this->productAttributeSets = array_flip($query->getAdapter()->fetchCol($query));
        }
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
        if (!isset($this->productAttributeSets[$recordToHandle->getValue('attribute_set_id')])) {
            $recordToHandle->setValue($this->field, null);
            return;
        }

        $newValue = preg_replace('/[^a-z0-9]+/', '-', strtolower($recordToHandle->getValue('attribute_group_name')));
        $newValue = ($newValue == 'migration-general') ? 'product-details' : $newValue;
        $recordToHandle->setValue($this->field, $newValue);
    }
}
