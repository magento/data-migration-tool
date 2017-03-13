<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to convert class names
 */
class ClassMap extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Migration\Reader\ClassMap
     */
    protected $classMap;

    /**
     * @param \Migration\Reader\ClassMap $classMap
     */
    public function __construct(
        \Migration\Reader\ClassMap $classMap
    ) {
        $this->classMap = $classMap;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $classOldFashion = $recordToHandle->getValue($this->field);
        $classNewStyle = $this->classMap->convertClassName($classOldFashion);
        $class = $classNewStyle ?: $classOldFashion;
        $recordToHandle->setValue($this->field, $class);
    }
}
