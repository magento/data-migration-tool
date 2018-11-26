<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;

/**
 * Handler to copy field value to some other field
 */
class FieldCopy extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $fieldCopy;

    /**
     * @param string $fieldCopy
     */
    public function __construct($fieldCopy)
    {
        $this->fieldCopy = $fieldCopy;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $fieldCopyValue = $recordToHandle->getValue($this->fieldCopy);
        if ($fieldCopyValue) {
            $recordToHandle->setValue($this->field, $fieldCopyValue);
        }
    }
}
