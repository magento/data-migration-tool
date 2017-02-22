<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttributeGroup;

use Migration\ResourceModel\Record;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;
use Migration\Model\Eav\AttributeGroupNameToCodeMap;

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
     * @var AttributeGroupNameToCodeMap
     */
    private $groupNameToCodeMap;

    /**
     * @param Config $config
     * @param Source $source
     * @param AttributeGroupNameToCodeMap $groupNameToCodeMap
     * @throws Exception
     */
    public function __construct(Config $config, Source $source, AttributeGroupNameToCodeMap $groupNameToCodeMap)
    {
        $this->groupNameToCodeMap = $groupNameToCodeMap;
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
        $groupCode = $this->groupNameToCodeMap->getGroupCodeMap($recordToHandle->getValue('attribute_group_name'));
        $recordToHandle->setValue($this->field, $groupCode);
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
