<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Resource\Record;

/**
 * Handler to convert class names
 */
class ClassMap extends AbstractHandler implements HandlerInterface
{
    /**
     * @var \Migration\ClassMap
     */
    protected $classMap;

    /**
     * @param \Migration\ClassMap $classMap
     */
    public function __construct(
        \Migration\ClassMap $classMap
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
