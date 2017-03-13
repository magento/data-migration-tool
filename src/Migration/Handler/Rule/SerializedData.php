<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\Rule;

use Migration\Reader\ClassMap;
use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;

/**
 * Class SerializedRules
 */
class SerializedData extends AbstractHandler
{
    /**
     * @var ClassMap
     */
    protected $classMap;

    /**
     * @param ClassMap $classMap
     */
    public function __construct(ClassMap $classMap)
    {
        $this->classMap = $classMap;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $unserializedData = unserialize($recordToHandle->getValue($this->field));
        if (is_array($unserializedData)) {
            $recordToHandle->setValue($this->field, serialize($this->replaceValues($unserializedData)));
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function replaceValues(array $data)
    {
        foreach ($data as &$value) {
            if (is_array($value)) {
                $value = $this->replaceValues($value);
            } elseif (is_string($value)) {
                $value = $this->classMap->convertClassName($value);
            }
        }

        return $data;
    }
}
