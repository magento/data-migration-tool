<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler\EavAttribute;

use Migration\Reader\ClassMap;
use Migration\Handler\AbstractHandler;
use Migration\ResourceModel\Record;

/**
 * Class ConvertClass
 */
class ConvertModel extends AbstractHandler
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
     * @param Record $recordToHandle
     * @param Record $oppositeRecord
     * @return mixed
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $sourceModel = $recordToHandle->getValue($this->field);
        $oppositeRecordValue = $oppositeRecord->getValue($this->field);
        if (empty($sourceModel) && !empty($oppositeRecordValue)) {
            $recordToHandle->setValue($this->field, $oppositeRecord->getValue($this->field));
        } elseif (empty($sourceModel)) {
            $recordToHandle->setValue($this->field, null);
        } else {
            $recordToHandle->setValue($this->field, $this->classMap->convertClassName($sourceModel));
        }
    }
}
