<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource\Adapter;

use Magento\Framework\DB\Ddl\Table;
use Migration\Resource\Document;

/**
 * Mysql adapter
 */
class Mysql implements \Migration\Resource\AdapterInterface
{
    const BACKUP_DOCUMENT_PREFIX = 'migration_backup_';
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $resourceAdapter;

    /**
     * @var \Magento\Framework\DB\Ddl\Trigger
     */
    protected $triggerFactory;

    /**
     * @var string
     */
    protected $schemaName  = null;

    /**
     * @var array
     */
    protected $triggers = [];

    /**
     * @param \Magento\Framework\DB\Adapter\Pdo\MysqlFactory $adapterFactory
     * @param \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
     * @param array $config
     */
    public function __construct(
        \Magento\Framework\DB\Adapter\Pdo\MysqlFactory $adapterFactory,
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory,
        array $config
    ) {
        $configData['config'] = $config;
        $this->resourceAdapter = $adapterFactory->create($configData);
        $this->resourceAdapter->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->triggerFactory = $triggerFactory;
    }

    /**
     * @inheritdoc
     */
    public function getDocumentStructure($documentName)
    {
        return $this->resourceAdapter->describeTable($documentName);
    }

    /**
     * @inheritdoc
     */
    public function getDocumentList()
    {
        return $this->resourceAdapter->listTables();
    }

    /**
     * @inheritdoc
     */
    public function getRecordsCount($documentName)
    {
        $select = $this->resourceAdapter->select();
        $select->from($documentName, 'COUNT(*)');
        $result = $this->resourceAdapter->fetchOne($select);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function loadPage($documentName, $pageNumber, $pageSize)
    {
        $select = $this->resourceAdapter->select();
        $select->from($documentName, '*')
            ->limit($pageSize, $pageNumber * $pageSize);
        $result = $this->resourceAdapter->fetchAll($select);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function insertRecords($documentName, $records)
    {
        $this->resourceAdapter->rawQuery("SET @OLD_INSERT_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $result = $this->resourceAdapter->insertMultiple($documentName, $records);
        $this->resourceAdapter->rawQuery("SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'')");
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function deleteAllRecords($documentName)
    {
        $this->resourceAdapter->truncateTable($documentName);
    }

    /**
     * Load data from DB Select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return array
     */
    public function loadDataFromSelect($select)
    {
        return $this->resourceAdapter->fetchAll($select);
    }

    /**
     * Get DB Select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect()
    {
        return $this->resourceAdapter->select();
    }

    /**
     * @param string $table
     * @param string $newTableName
     * @return Table
     */
    public function getTableDdlCopy($table, $newTableName)
    {
        return $this->resourceAdapter->createTableByDdl($table, $newTableName);
    }

    /**
     * @param Table $tableDdl
     * @return void
     */
    public function createTableByDdl($tableDdl)
    {
        $this->resourceAdapter->dropTable($tableDdl->getName());
        $this->resourceAdapter->createTable($tableDdl);
        $this->resourceAdapter->resetDdlCache($tableDdl->getName());
    }

    /**
     * Updates document rows with specified data based on a WHERE clause.
     *
     * @param mixed $document
     * @param array $bind
     * @param mixed $where
     * @return int
     */
    public function updateDocument($document, array $bind, $where = '')
    {
        return $this->resourceAdapter->update($document, $bind, $where);
    }

    /**
     * @inheritdoc
     */
    public function backupDocument($documentName)
    {
        $backupTableName = self::BACKUP_DOCUMENT_PREFIX . $documentName;
        $tableCopy = $this->getTableDdlCopy($documentName, $backupTableName);
        if (!$this->resourceAdapter->isTableExists($backupTableName)) {
            $this->createTableByDdl($tableCopy);
            $select = $this->resourceAdapter->select()->from($documentName);
            $query = $this->resourceAdapter->insertFromSelect($select, $tableCopy->getName());
            $this->resourceAdapter->query($query);
        }
    }

    /**
     * @inheritdoc
     */
    public function rollbackDocument($documentName)
    {
        $backupTableName = self::BACKUP_DOCUMENT_PREFIX . $documentName;
        if ($this->resourceAdapter->isTableExists($backupTableName)) {
            $this->resourceAdapter->truncateTable($documentName);
            $select = $this->resourceAdapter->select()->from($backupTableName);
            $query = $this->resourceAdapter->insertFromSelect($select, $documentName);
            $this->resourceAdapter->query($query);
            $this->resourceAdapter->dropTable($backupTableName);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteBackup($documentName)
    {
        $backupTableName = self::BACKUP_DOCUMENT_PREFIX . $documentName;
        if ($this->resourceAdapter->isTableExists($backupTableName)) {
            $this->resourceAdapter->dropTable($backupTableName);
        }
    }

    /**
     * Create delta for specified table
     *
     * @param string $documentName
     * @param string $changeLogName
     * @param string $idKey
     */
    public function createDelta($documentName, $changeLogName, $idKey)
    {
        if (!$this->resourceAdapter->isTableExists($changeLogName)) {
            $triggerTable = $this->resourceAdapter->newTable($changeLogName)
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
                )->addColumn(
                    'operation',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
                );
            $this->resourceAdapter->createTable($triggerTable);
        }
        foreach (\Magento\Framework\DB\Ddl\Trigger::getListOfEvents() as $event) {
            $triggerName = 'trg_' . $documentName . '_' . strtolower($event);
            $statement = $this->buildStatement($event, $idKey, $changeLogName);
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($this->resourceAdapter->getTableName($documentName));;
            if ($this->isTriggerExist($triggerName)) {
                $oldTriggerStatement = $this->triggers[$triggerName]['action_statement'];
                $trigger->addStatement($oldTriggerStatement);
                $this->resourceAdapter->dropTrigger($triggerName);
            }
            $trigger->addStatement($statement);
            $this->resourceAdapter->createTrigger($trigger);
            unset($trigger);
        }
    }

    /**
     * @param string $event
     * @param string $idKey
     * @param string $triggerTableName
     * @return string
     */
    protected function buildStatement($event, $idKey, $triggerTableName)
    {
        $entityTime = ($event == \Magento\Framework\DB\Ddl\Trigger::EVENT_DELETE) ? 'OLD' : 'NEW';
        return "INSERT INTO $triggerTableName VALUES ($entityTime.$idKey, '$event')"
        ."ON DUPLICATE KEY UPDATE operation = '$event'";
    }

    /**
     * @param $triggerName
     * @return bool
     */
    protected function isTriggerExist($triggerName)
    {
        if (!isset($this->triggers[$triggerName])) {
            $this->getTriggers();
        }

        if (isset($this->triggers[$triggerName])) {
            return true;
        }

        return false;
    }

    /**
     * Get all database triggers
     *
     * @return void
     */
    protected function getTriggers(){
        $columns = array(
            'TRIGGER_NAME',
            'EVENT_MANIPULATION',
            'EVENT_OBJECT_CATALOG',
            'EVENT_OBJECT_SCHEMA',
            'EVENT_OBJECT_TABLE',
            'ACTION_ORDER',
            'ACTION_CONDITION',
            'ACTION_STATEMENT',
            'ACTION_ORIENTATION',
            'ACTION_TIMING',
            'ACTION_REFERENCE_OLD_TABLE',
            'ACTION_REFERENCE_NEW_TABLE',
            'ACTION_REFERENCE_OLD_ROW',
            'ACTION_REFERENCE_NEW_ROW',
            'CREATED',
        );
        $sql = 'SELECT ' . implode(', ', $columns)
            . ' FROM ' . $this->resourceAdapter->quoteIdentifier(array('INFORMATION_SCHEMA','TRIGGERS'))
            . ' WHERE ';

        $schema = $this->getSchemaName();
        if ($schema) {
            $sql .= $this->resourceAdapter->quoteIdentifier('TRIGGER_SCHEMA')
                . ' = ' . $this->resourceAdapter->quote($schema);
        } else {
            $sql .= $this->resourceAdapter->quoteIdentifier('TRIGGER_SCHEMA')
                . ' != ' . $this->resourceAdapter->quote('INFORMATION_SCHEMA');
        }

        $results = $this->resourceAdapter->query($sql);

        $data = array();
        foreach ($results as $row) {
            $row = array_change_key_case($row, CASE_LOWER);
            $row['action_statement'] = $this->convertStatement($row['action_statement']);
            if (null !== $row['created']) {
                $row['created'] = new DateTime($row['created']);
            }
            $data[$row['trigger_name']] = $row;
        }
        $this->triggers = $data;
    }

    /**
     * @param string $row
     * @return mixed
     */
    protected function convertStatement($row)
    {
        $regex = '/(BEGIN)([\s\S]*?)(END.?)/';
        return preg_replace($regex, '$2', $row);
    }

    /**
     * Returns current schema name
     *
     * @return string
     */
    protected function getCurrentSchema()
    {
        return $this->resourceAdapter->fetchOne('SELECT SCHEMA()');
    }

    /**
     * Returns schema name
     *
     * @return string
     */
    protected function getSchemaName()
    {
        if (!$this->schemaName) {
            $this->schemaName = $this->getCurrentSchema();
        }

        return $this->schemaName;
    }
}
