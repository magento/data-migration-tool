<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\Resource\Record;

/**
 * Handler to set constant value to the field
 */
class GetDestinationValue extends AbstractHandler implements HandlerInterface
{
    /**
     * @var bool
     */
    protected $allowNull;

    /**
     * @param bool $allowNull
     */
    public function __construct($allowNull = true)
    {
        $this->allowNull = $allowNull === 'false' ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        if (!is_null($oppositeRecord->getValue($this->field)) || $this->allowNull) {
            $recordToHandle->setValue($this->field, $oppositeRecord->getValue($this->field));
        }
    }
}
