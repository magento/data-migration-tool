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
class SetValue extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = (strtoupper($value) === 'NULL') ? null : $value;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $valueStored = $recordToHandle->getValue($this->field);
        $value = $this->value;
        if (is_string($value)) {
            $operator = substr($value, 0, 1);
            $value = substr($value, 1);
            switch ($operator) {
                case '+':
                    $value = $valueStored + $value;
                    break;
                case '-';
                    $value = $valueStored - $value;
                    break;
                default:
                    $value = $this->value;
            }
        }
        $recordToHandle->setValue($this->field, $value);
    }
}
