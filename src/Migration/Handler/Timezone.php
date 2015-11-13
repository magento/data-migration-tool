<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Handler to set hash value to the field, based on other field
 */
class Timezone extends AbstractHandler implements HandlerInterface
{
    const MIN_OFFSET = -14;

    const MAX_OFFSET = 12;

    const SIGN_PLUS  = '+';

    const SIGN_MINUS = '-';

    /**
     * @var string
     */
    protected $offset;

    /**
     * @param string $offset
     */
    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);

        $value  = $recordToHandle->getValue($this->field);
        if (!$value || !$this->offset) {
            return;
        }
        $dateTime = new \DateTime($value);
        $dateTime->modify($this->offset . ' hour');

        $recordToHandle->setValue($this->field, $dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Record $record)
    {
        $offsetInt  = $this->offset;

        if (in_array($sign = substr($this->offset, 0, 1), [self::SIGN_PLUS, self::SIGN_MINUS])) {
            $offsetInt = substr($this->offset, 1, strlen($this->offset) - 1);
        } else {
            $sign = self::SIGN_PLUS;
        }

        if ((self::SIGN_PLUS === $sign) && ($offsetInt > self::MAX_OFFSET)
         || (self::SIGN_MINUS === $sign) && ($offsetInt < self::MIN_OFFSET)) {
            throw new Exception(
                'Offset can have value between '
                . '"' . self::MIN_OFFSET . '" and "' . self::SIGN_PLUS . self::MAX_OFFSET . '""'
            );
        }

        parent::validate($record);
    }
}
