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
    protected $setNullIfEmpty;

    /**
     * @param bool $setNullIfEmpty
     */
    public function __construct($setNullIfEmpty = false)
    {
        $this->setNullIfEmpty = $setNullIfEmpty === 'true' ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        if ($oppositeRecord->getValue($this->field) !== null || $this->setNullIfEmpty) {
            $recordToHandle->setValue($this->field, $oppositeRecord->getValue($this->field));
        }
    }
}
