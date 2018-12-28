<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $sourceModel = $recordToHandle->getValue($this->field);
        $destinationModel = $oppositeRecord->getValue($this->field);
        $sourceModelConverted = $this->classMap->convertClassName($sourceModel);
        if (empty($sourceModel) && !empty($destinationModel)) {
            $recordToHandle->setValue($this->field, $destinationModel);
        } elseif (empty($sourceModel) || empty($sourceModelConverted)) {
            $recordToHandle->setValue($this->field, null);
        } else {
            $recordToHandle->setValue($this->field, $sourceModelConverted);
        }
    }
}
