<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel\Adapter;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Ddl\Trigger;

/**
 * Mysql adapter
 */
class Mysql implements \Migration\ResourceModel\AdapterInterface
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
     * @param \Migration\ResourceModel\Adapter\Pdo\MysqlBuilder $mysqlBuilder
     * @param \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
     * @param string $resourceType
     */
    public function __construct(
        \Migration\ResourceModel\Adapter\Pdo\MysqlBuilder $mysqlBuilder,
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory,
        $resourceType
    ) {
        $this->resourceAdapter = $mysqlBuilder->build($resourceType);
        $this->setForeignKeyChecks(0);
        $this->triggerFactory = $triggerFactory;
    }

    /**
     * Set foreign key checks
     *
     * @param int $value
     * @return void
     */
    public function setForeignKeyChecks($value)
    {
        $value = (int) $value;
        $this->resourceAdapter->query("SET FOREIGN_KEY_CHECKS={$value};");
    }

    /**
     * Retrieve the foreign keys descriptions for a $documentName table
     *
     * @param string $documentName
     * @return array
     */
    public function getForeignKeys($documentName)
    {
        return $this->resourceAdapter->getForeignKeys($documentName);
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
    public function getRecordsCount($documentName, $distinctFields = [])
    {
        $distinctFields = ($distinctFields && is_array($distinctFields))
            ? 'DISTINCT ' . implode(',', $distinctFields)
            : '*';
        $select = $this->getSelect();
        $select->from($documentName, 'COUNT(' . $distinctFields . ')');
        $result = $this->resourceAdapter->fetchOne($select);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function loadPage(
        $documentName,
        $pageNumber,
        $pageSize,
        $identityField = null,
        $identityId = null,
        \Zend_Db_Expr $condition = null
    ) {
        $select = $this->getSelect();
        $select->from($documentName, '*');
        if ($identityField && $identityId !== null) {
            $select->where("`$identityField` >= ?", ($identityId == 0 ? $identityId : $identityId + 1));
            $select->limit($pageSize);
            $select->order("$identityField ASC");
        } else {
            $select->limit($pageSize, $pageNumber * $pageSize);
        }
        if ($condition) {
            $select->where($condition);
        }
        $result = $this->resourceAdapter->fetchAll($select);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function insertRecords($documentName, $records, $updateOnDuplicate = false)
    {
        $this->resourceAdapter->rawQuery("SET @OLD_INSERT_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        if ($updateOnDuplicate) {
            if (is_array($updateOnDuplicate)) {
                $result = $this->resourceAdapter->insertOnDuplicate($documentName, $records, $updateOnDuplicate);
            } else {
                $result = $this->resourceAdapter->insertOnDuplicate($documentName, $records);
            }
        } else if (!is_array(reset($records))) {
            $result = $this->resourceAdapter->insert($documentName, $records);
        } else {
            $result = $this->insertMultiple($documentName, $records);
        }
        $this->resourceAdapter->rawQuery("SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'')");

        return $result;
    }

    /**
     * Insert multiple
     *
     * @param string $documentName
     * @param array $records
     * @return bool
     */
    protected function insertMultiple($documentName, $records)
    {
        $bind = [];
        $values = [];
        $colNum = count($records[0]);
        $fields = array_keys($records[0]);
        foreach ($records as $record) {
            foreach ($record as $value) {
                $bind[] = $value;
            }
            $values[] = '(' . implode(',', array_fill(0, $colNum, '?')) . ')';
        }
        if ($values && $fields) {
            $insertSql = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                $documentName,
                sprintf('`%s`', implode('`,`', $fields)),
                implode(',', $values)
            );
            $statement = $this->resourceAdapter->getConnection()->prepare($insertSql);
            $statement->execute($bind);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select, $table, array $fields = [], $mode = false)
    {
        $this->resourceAdapter->rawQuery("SET @OLD_INSERT_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
        $query = $this->resourceAdapter->insertFromSelect($select, $table, $fields, $mode);
        $this->resourceAdapter->query($query);
        $this->resourceAdapter->rawQuery("SET SQL_MODE=IFNULL(@OLD_INSERT_SQL_MODE,'')");
    }

    /**
     * @inheritdoc
     */
    public function deleteAllRecords($documentName)
    {
        $this->resourceAdapter->truncateTable($documentName);
    }

    /**
     * @inheritdoc
     */
    public function deleteRecords($documentName, $idKeys, $items)
    {
        if (is_string($idKeys)) {
            $items = implode("','", $items);
            $this->resourceAdapter->delete($documentName, "$idKeys IN ('$items')");
        } else if (count($idKeys) == 1 && is_array($items)) {
            $idKey = array_shift($idKeys);
            $items = array_column($items, $idKey);
            $items = implode("','", $items);
            $this->resourceAdapter->delete($documentName, "$idKey IN ('$items')");
        } else if (is_array($idKeys) && is_array($items)) {
            foreach ($items as $item) {
                $andFields = [];
                foreach ($idKeys as $idKey) {
                    $andFields[] = "$idKey = $item[$idKey]";
                }
                $this->resourceAdapter->delete($documentName, implode(' AND ', $andFields));
            }
        }
    }

    /**
     * Delete processed records
     *
     * @param string $documentName
     * @return void
     */
    public function deleteProcessedRecords($documentName)
    {
        $this->resourceAdapter->delete($documentName, "`processed` = 1");
    }

    /**
     * @inheritdoc
     */
    public function loadChangedRecords(
        $documentName,
        $deltaLogName,
        $idKeys,
        $pageNumber,
        $pageSize,
        $getProcessed = false
    ) {
        $andFields = [];
        foreach ($idKeys as $idKey) {
            $andFields[] = "$documentName.$idKey = $deltaLogName.$idKey";
        }
        $select = $this->getSelect();
        $select->from($deltaLogName, [])
            ->join($documentName, implode(' AND ', $andFields), '*')
            ->where("`operation` in ('INSERT', 'UPDATE')")
            ->limit($pageSize, $pageNumber * $pageSize);
        if (!$getProcessed) {
            $select->where("`processed` != 1");
        }
        $result = $this->resourceAdapter->fetchAll($select);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function loadDeletedRecords($deltaLogName, $idKeys, $pageNumber, $pageSize, $getProcessed = false)
    {
        $select = $this->getSelect();
        $select->from($deltaLogName, $idKeys)
            ->where("`operation` = 'DELETE'")
            ->limit($pageSize, $pageNumber * $pageSize);
        if (!$getProcessed) {
            $select->where("`processed` != 1");
        }
        return $this->resourceAdapter->fetchAll($select);
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
     * Get table ddl copy
     *
     * @param string $table
     * @param string $newTableName
     * @return Table
     */
    public function getTableDdlCopy($table, $newTableName)
    {
        return $this->resourceAdapter->createTableByDdl($table, $newTableName);
    }

    /**
     * Create table by ddl
     *
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
     * Updates document rows with specified data based on a WHERE clause
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
    public function updateChangedRecords($document, $data, $updateOnDuplicate = false)
    {
        if (is_array($updateOnDuplicate) && !empty($updateOnDuplicate)) {
            $result = $this->resourceAdapter->insertOnDuplicate($document, $data, $updateOnDuplicate);
        } else {
            $result = $this->resourceAdapter->insertOnDuplicate($document, $data);
        }
        return $result;
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
            $select = $this->getSelect()->from($documentName);
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
            $select = $this->getSelect()->from($backupTableName);
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
     * @param string $deltaLogName
     * @param array $idKeys
     * @return boolean
     */
    public function createDelta($documentName, $deltaLogName, $idKeys)
    {
        $deltaCreated = true;
        if (!$this->resourceAdapter->isTableExists($deltaLogName)) {
            $triggerTable = $this->resourceAdapter->newTable($deltaLogName);
            foreach ($idKeys as $idKey) {
                $triggerTable->addColumn(
                    $idKey,
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'primary' => true]
                );
            }
            $triggerTable->addColumn(
                    'operation',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
                )->addColumn(
                    'processed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 0]
                );
            $this->resourceAdapter->createTable($triggerTable);
            $deltaCreated = $this->resourceAdapter->isTableExists($deltaLogName);
        } else {
            $this->deleteAllRecords($deltaLogName);
        }
        foreach (Trigger::getListOfEvents() as $event) {
            $triggerName = $this->resourceAdapter->getTableName(
                'trg_' . $documentName . '_after_' . strtolower($event)
            );
            $statement = $this->buildStatement($event, $idKeys, $deltaLogName);
            $trigger = $this->triggerFactory->create()
                ->setTime(Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($documentName);
            $triggerKey = $documentName . $event . Trigger::TIME_AFTER;
            $triggerExists = $this->isTriggerExist($triggerKey);
            if ($triggerExists) {
                $triggerName = $this->triggers[$triggerKey]['trigger_name'];
                $oldTriggerStatement = $this->triggers[$triggerKey]['action_statement'];
                if (strpos($oldTriggerStatement, $statement) !== false) {
                    unset($trigger);
                    continue;
                }
                $trigger->addStatement($oldTriggerStatement);
                $this->resourceAdapter->dropTrigger($triggerName);
            }
            $trigger->addStatement($statement)->setName($triggerName);
            $this->resourceAdapter->createTrigger($trigger);
            if (!$triggerExists) {
                $this->triggers[$triggerKey] = 1;
            }
            unset($trigger);
        }
        return $deltaCreated;
    }

    /**
     * Build statement
     *
     * @param string $event
     * @param array $idKeys
     * @param string $triggerTableName
     * @return string
     */
    protected function buildStatement($event, $idKeys, $triggerTableName)
    {
        $idKeysCol = '';
        $idKeysValue = '';
        $entityTime = ($event == Trigger::EVENT_DELETE) ? 'OLD' : 'NEW';
        foreach ($idKeys as $idKey) {
            $idKeysCol .= "`$idKey`,";
            $idKeysValue .= "$entityTime.$idKey,";
        }
        return "INSERT INTO $triggerTableName ($idKeysCol `operation`) VALUES ($idKeysValue '$event')"
            . "ON DUPLICATE KEY UPDATE operation = '$event'";
    }

    /**
     * Is trigger exist
     *
     * @param string $triggerKey
     * @return bool
     */
    protected function isTriggerExist($triggerKey)
    {
        if (empty($this->triggers)) {
            $this->loadTriggers();
        }

        if (isset($this->triggers[$triggerKey])) {
            return true;
        }

        return false;
    }

    /**
     * Get all database triggers
     *
     * @return void
     */
    protected function loadTriggers()
    {
        $schema = $this->getSchemaName();
        if ($schema) {
            $sqlFilter = $this->resourceAdapter->quoteIdentifier('TRIGGER_SCHEMA')
                . ' = ' . $this->resourceAdapter->quote($schema);
        } else {
            $sqlFilter = $this->resourceAdapter->quoteIdentifier('TRIGGER_SCHEMA')
                . ' != ' . $this->resourceAdapter->quote('INFORMATION_SCHEMA');
        }
        $select = $this->getSelect()
            ->from(new \Zend_Db_Expr($this->resourceAdapter->quoteIdentifier(['INFORMATION_SCHEMA', 'TRIGGERS'])))
            ->where($sqlFilter);
        $results = $this->resourceAdapter->query($select);
        $data = [];
        foreach ($results as $row) {
            $row = array_change_key_case($row, CASE_LOWER);
            $row['action_statement'] = $this->convertStatement($row['action_statement']);
            $key = $row['event_object_table'] . $row['event_manipulation'] . $row['action_timing'];
            $data[$key] = $row;
        }
        $this->triggers = $data;
    }

    /**
     * Convert statement
     *
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
