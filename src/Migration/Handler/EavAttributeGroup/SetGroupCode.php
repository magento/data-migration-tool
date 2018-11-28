<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    private $canStart;

    /**
     * @var AttributeGroupNameToCodeMap
     */
    private $groupNameToCodeMap;

    /**
     * @var Source
     */
    private $source;

    /**
     * @param Config $config
     * @param AttributeGroupNameToCodeMap $groupNameToCodeMap
     * @param Source $source
     * @throws Exception
     */
    public function __construct(Config $config, AttributeGroupNameToCodeMap $groupNameToCodeMap, Source $source)
    {
        $this->groupNameToCodeMap = $groupNameToCodeMap;
        $this->canStart = $config->getSource()['type'] == DatabaseStage::SOURCE_TYPE;
        $this->source = $source;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        if (!$this->canStart) {
            return;
        }
        $this->validate($recordToHandle);
        $entityType = $this->determineEntityType($recordToHandle->getValue('attribute_set_id'));
        $groupCode = $this->groupNameToCodeMap->getGroupCodeMap(
            $recordToHandle->getValue('attribute_group_name'),
            $entityType
        );
        $recordToHandle->setValue($this->field, $groupCode);
    }

    /**
     * Find entity type by attribute set ID
     *
     * @param string $attributeSetId
     * @return string
     */
    private function determineEntityType($attributeSetId)
    {
        /** @var Mysql $adapter */
        $adapter = $this->source->getAdapter();
        $select = $adapter->getSelect()->from(
            ['eas' => $this->source->addDocumentPrefix('eav_attribute_set')],
            []
        )->join(
            ['eet' => $this->source->addDocumentPrefix('eav_entity_type')],
            'eas.entity_type_id = eet.entity_type_id',
            ['entity_type_code']
        )->where('eas.attribute_set_id = ?', $attributeSetId);
        return $select->getAdapter()->fetchOne($select);
    }
}
