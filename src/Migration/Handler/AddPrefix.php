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
class AddPrefix extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);
        $recordToHandle->setValue($this->field, $this->prefix . $recordToHandle->getValue($this->field));
    }
}
