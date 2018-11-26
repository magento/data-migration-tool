<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\Config;
use Migration\Exception;
use Migration\Step\DatabaseStage;

/**
 * Class ConverEavValue
 */
class ConvertEavValue extends AbstractHandler implements HandlerInterface
{
    /**
     * Map data
     *
     * @var array
     */
    protected $map;

    /**
     * Attribute IDs
     *
     * @var array
     */
    protected $attributeIds;

    /**
     * Can start
     *
     * @var bool
     */
    protected $canStart;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string
     */
    protected $attributeCode;

    /**
     * @param Config $config
     * @param Source $source
     * @param string $map
     * @param string $attributeCode
     * @throws Exception
     */
    public function __construct(Config $config, Source $source, $map, $attributeCode)
    {
        $this->source = $source;
        $this->attributeCode = $attributeCode;
        $this->canStart = $config->getSource()['type'] == DatabaseStage::SOURCE_TYPE;
        if ($this->canStart) {
            $map = rtrim($map, ']');
            $map = ltrim($map, '[');
            $map = explode(';', $map);
            $resultMap = [];
            foreach ($map as $mapRecord) {
                $explodedRecord = explode(':', trim($mapRecord));
                if (count($explodedRecord) != 2) {
                    throw new Exception('Invalid map provided to convert handler');
                }
                list($key, $value) = $explodedRecord;
                $resultMap[$key] = $value;
            }
            $this->map = $resultMap;
        }
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
        $attributeIds = $this->getAttributeIds();
        if (isset($attributeIds[$recordToHandle->getValue('attribute_id')])) {
            $value = $recordToHandle->getValue($this->field);
            if (isset($this->map[$value])) {
                $value = $this->map[$value];
            }
            $recordToHandle->setValue($this->field, $value);
        }
    }

    /**
     * Get attribute ids
     *
     * @return array
     */
    protected function getAttributeIds()
    {
        if (empty($this->attributeIds)) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from($this->source->addDocumentPrefix('eav_attribute'), ['attribute_id'])
                    ->where('attribute_code = ?', $this->attributeCode);
            $this->attributeIds = array_flip($query->getAdapter()->fetchCol($query));
        }
        return $this->attributeIds;
    }
}
