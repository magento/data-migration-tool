<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\DB\Adapter\Pdo;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Class Mysql
 */
class Mysql extends \Magento\Framework\DB\Adapter\Pdo\Mysql
{
    /**
     * @var array
     */
    private $initSelectParts = [];

    /**
     * @param StringUtils $string
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param SelectFactory $selectFactory
     * @param array $config
     */
    public function __construct(
        StringUtils $string,
        DateTime $dateTime,
        LoggerInterface $logger,
        SelectFactory $selectFactory,
        array $config = []
    ) {
        parent::__construct($string, $dateTime, $logger, $selectFactory, $config['database']);
        $this->initSelectParts = (isset($config['init_select_parts']) && is_array($config['init_select_parts']))
            ? $config['init_select_parts']
            : [];
    }

    /**
     * @return Select
     */
    public function select()
    {
        $select = parent::select();
        foreach ($this->initSelectParts as $partKey => $partValue) {
            $select->setPart($partKey, $partValue);
        }
        return $select;
    }

    /**
     * @param int $value
     * @return void
     */
    public function setForeignKeyChecks($value)
    {
        $value = (int)$value;
        $this->query("SET FOREIGN_KEY_CHECKS={$value};");
    }
}
