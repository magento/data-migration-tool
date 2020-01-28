<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Config;
use Migration\ResourceModel\Adapter\Mysql;
use Migration\ResourceModel\Record;
use Migration\ResourceModel\Source;
use Migration\Step\DatabaseStage;

/**
 * Class TruncateSuffix
 */
class TruncateSuffix extends AbstractHandler implements HandlerInterface
{
    const DEFAULT_SUFFIX = '.html';

    /**
     * Attribute IDs
     *
     * @var array
     */
    protected $attributeIds;

    /**
     * @var string
     */
    protected $suffixPath;

    /**
     * @var string
     */
    protected $suffix = false;

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
    protected $attributeCodes;

    /**
     * @var string
     */
    protected $entityTypeCode;

    /**
     * TruncateSuffix constructor.
     * @param Config $config
     * @param Source $source
     * @param string $suffixPath
     * @param string $attributeCodes
     * @param string $entityTypeCode
     */
    public function __construct(Config $config, Source $source, $suffixPath, $attributeCodes, $entityTypeCode)
    {
        $this->canStart = $config->getSource()['type'] == DatabaseStage::SOURCE_TYPE;
        if ($this->canStart) {
            $this->source = $source;
            $this->suffixPath = $suffixPath;
            $attributeCodes = rtrim($attributeCodes, ']');
            $attributeCodes = ltrim($attributeCodes, '[');
            $this->attributeCodes = explode(',', $attributeCodes);
            $this->entityTypeCode = $entityTypeCode;
        }
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        if (!$this->canStart || !$this->getSuffix()) {
            return;
        }
        $attributeIds = $this->getAttributeIds();
        if (in_array($recordToHandle->getValue('attribute_id'), $attributeIds)) {
            $suffix = '~' . preg_quote($this->getSuffix()) . '$~';
            $value = $recordToHandle->getValue($this->field);
            $value = preg_replace($suffix, '', $value);
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
        if (null === $this->attributeIds) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from(
                ['ea' => $this->source->addDocumentPrefix('eav_attribute')],
                ['ea.attribute_id']
            )->join(
                ['eet' => $this->source->addDocumentPrefix('eav_entity_type')],
                'ea.entity_type_id = eet.entity_type_id',
                []
            )
                ->where('ea.attribute_code IN (?)', $this->attributeCodes)
                ->where('eet.entity_type_code = ?', $this->entityTypeCode);
            $this->attributeIds = $query->getAdapter()->fetchCol($query);
        }
        return $this->attributeIds;
    }

    /**
     * Get suffix
     *
     * @return string
     */
    protected function getSuffix()
    {
        if (false === $this->suffix) {
            /** @var Mysql $adapter */
            $adapter = $this->source->getAdapter();
            $query = $adapter->getSelect()->from($this->source->addDocumentPrefix('core_config_data'), ['value'])
                ->where('path = ?', $this->suffixPath)
                ->limit(1);
            $result = $query->getAdapter()->fetchOne($query);
            $this->suffix = $result ?: self::DEFAULT_SUFFIX;
        }
        return $this->suffix;
    }
}
