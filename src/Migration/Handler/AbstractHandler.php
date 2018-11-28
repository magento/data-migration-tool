<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Class AbstractHandler
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * Field, processed by the handler
     *
     * @var string
     */
    protected $field;

    /**
     * @inheritdoc
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(Record $record)
    {
        if (!in_array($this->field, $record->getFields())) {
            throw new Exception("{$this->field} field not found in the record.");
        }
    }
}
