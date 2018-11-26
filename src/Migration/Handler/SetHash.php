<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Handler to set hash value to the field, based on other field
 */
class SetHash extends AbstractHandler implements HandlerInterface
{
    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $baseField;

    /**
     * @var array
     */
    protected $supportedHashAlgorithms = ['crc32'];

    /**
     * @param string $hash
     * @param string $baseField
     */
    public function __construct($hash, $baseField)
    {
        $this->hash      = $hash;
        $this->baseField = $baseField;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);

        $oppositeValue  = $oppositeRecord->getValue($this->baseField);
        $hashMethod     = $this->hash;
        $resultValue    = $hashMethod($oppositeValue);

        $recordToHandle->setValue($this->field, $resultValue);
    }

    /**
     * @inheritdoc
     */
    public function validate(Record $record)
    {
        if (!in_array($this->hash, $this->supportedHashAlgorithms)) {
            throw new Exception("{$this->hash} hash algorithm is not supported.");
        }
        parent::validate($record);
    }
}
