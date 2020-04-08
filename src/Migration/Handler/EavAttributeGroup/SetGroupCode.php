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
use Magento\Framework\Filter\Translit;

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
     * @var Source
     */
    private $source;

    /**
     * @var Translit
     */
    private $translitFilter;

    /**
     * @param Config $config
     * @param Translit $translitFilter
     * @param Source $source
     * @throws Exception
     */
    public function __construct(Config $config, Translit $translitFilter, Source $source)
    {
        $this->canStart = $config->getSource()['type'] == DatabaseStage::SOURCE_TYPE;
        $this->translitFilter = $translitFilter;
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
        $groupName = $recordToHandle->getValue('attribute_group_name');
        $groupCode = trim(
            preg_replace(
                '/[^a-z0-9]+/',
                '-',
                $this->translitFilter->filter(strtolower($groupName))
            ),
            '-'
        );
        $groupCode = empty($groupCode) ? md5($groupCode) : $groupCode;
        $recordToHandle->setValue($this->field, $groupCode);
    }
}
