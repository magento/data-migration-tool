<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to set constant value to the field
 */
class Placeholder extends AbstractHandler implements HandlerInterface
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
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $content = $recordToHandle->getValue($this->field);
        if ($this->hasPlaceholders($content)) {
            $content = $this->processContent($content);
        }
        $recordToHandle->setValue($this->field, $content);
    }

    /**
     * Whether widget content has placeholders
     *
     * @param string $content
     * @return int
     */
    protected function hasPlaceholders($content)
    {
        return preg_match('/({{widget|{{block).*}}/mU', $content);
    }

    /**
     * Process widget placeholders content
     *
     * @param string $content
     * @return mixed
     */
    protected function processContent($content)
    {
        $classSource = [];
        $classDestination = [];
        foreach ($this->classMap->getMap() as $classOldFashion => $classNewStyle) {
            $classSource[] = sprintf('type="%s"', $classOldFashion);
            $classDestination[] = sprintf('type="%s"', str_replace('\\', '\\\\', $classNewStyle));
        }
        $content = str_replace($classSource, $classDestination, $content);
        // cut off name of a module from template path
        $content = preg_replace('/({{widget|{{block)(.*template=")(.*\/)(.*".*}})/mU', '$1$2$4', $content);
        // remove all unknown widgets
        $content = preg_replace('/{{widget type="[\w\-_]+\/[\w\-_]+".+}}/mU', '', $content);
        return $content;
    }
}
