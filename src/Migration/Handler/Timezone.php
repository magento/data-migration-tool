<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Handler;

use Migration\ResourceModel\Record;
use Migration\Exception;

/**
 * Handler to set offset for date and time fields while migration
 */
class Timezone extends AbstractHandler implements HandlerInterface
{
    const MIN_OFFSET = -14;

    const MAX_OFFSET = 12;

    const SIGN_PLUS  = '+';

    const SIGN_MINUS = '-';

    const TYPE_INT = 'int';

    /**
     * @var string
     */
    protected $offset;

    /**
     * @var array
     */
    protected $supportedDatatypes = [
        self::TYPE_INT,
        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
        \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
        \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
    ];

    /**
     * @param string $offset
     */
    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @inheritdoc
     */
    public function handle(Record $recordToHandle, Record $oppositeRecord)
    {
        $this->validate($recordToHandle);

        $value  = $recordToHandle->getValue($this->field);

        if (!$value || !$this->offset) {
            return;
        }

        $fieldType = $recordToHandle->getStructure()->getFields()[$this->field]['DATA_TYPE'];
        $isTypeInt = in_array($fieldType, [self::TYPE_INT, \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER]);

        if ($isTypeInt) {
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($value);
        } else {
            $dateTime = new \DateTime($value);
        }

        $dateTime->modify($this->offset . ' hour');

        if ($isTypeInt) {
            $value = $dateTime->getTimestamp();
        } else {
            $value = $dateTime->format(\Magento\Framework\DB\Adapter\Pdo\Mysql::TIMESTAMP_FORMAT);
        }

        $recordToHandle->setValue($this->field, $value);
    }

    /**
     * @inheritdoc
     */
    public function validate(Record $record)
    {
        $offsetInt  = $this->offset;

        $sign = substr($this->offset, 0, 1);
        if (in_array($sign, [self::SIGN_PLUS, self::SIGN_MINUS])) {
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

        $fieldType = $record->getStructure()->getFields()[$this->field]['DATA_TYPE'];
        if (!in_array($fieldType, $this->supportedDatatypes)) {
            throw new Exception('Provided datatype for field "' . $this->field . '" is not supported');
        }

        parent::validate($record);
    }
}
