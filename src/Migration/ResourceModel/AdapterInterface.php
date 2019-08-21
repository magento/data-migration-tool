<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\ResourceModel;

use Migration\ResourceModel\Document;

/**
 * Adapter interface class
 */
interface AdapterInterface
{
    /**
     * Get Documents list
     *
     * @return array
     */
    public function getDocumentList();

    /**
     * Get Document Fields list
     * Returns array:
     * [
     *     'fieldName' => 'options'
     * ]
     *
     * @param string $documentName
     * @return array
     */
    public function getDocumentStructure($documentName);

    /**
     * Returns number of records of document
     *
     * @param string $documentName
     * @param array|bool $distinctFields
     * @return int
     */
    public function getRecordsCount($documentName, $distinctFields = []);

    /**
     * Load Page
     *
     * @param string $documentName
     * @param int $pageNumber
     * @param int $pageSize
     * @param string $identityField
     * @param int $identityId
     * @param \Zend_Db_Expr $condition
     * @return array
     */
    public function loadPage(
        $documentName,
        $pageNumber,
        $pageSize,
        $identityField = null,
        $identityId = null,
        \Zend_Db_Expr $condition = null
    );

    /**
     * Insert records into document
     *
     * @param string $documentName
     * @param array $records
     * @param bool|array $updateOnDuplicate
     * @return int
     */
    public function insertRecords($documentName, $records, $updateOnDuplicate = false);

    /**
     * Delete all record from the document
     *
     * @param string $documentName
     * @return void
     */
    public function deleteAllRecords($documentName);

    /**
     * Delete records
     *
     * @param string $documentName
     * @param string|array $idKeys
     * @param array $items
     * @return void
     */
    public function deleteRecords($documentName, $idKeys, $items);

    /**
     * Load page with changed records from the document
     *
     * @param string $documentName
     * @param string $deltaLogName
     * @param array $idKeys
     * @param int $pageNumber
     * @param int $pageSize
     * @param bool|false $getProcessed
     * @return array
     */
    public function loadChangedRecords(
        $documentName,
        $deltaLogName,
        $idKeys,
        $pageNumber,
        $pageSize,
        $getProcessed = false
    );

    /**
     * Load page with changed records from the document
     *
     * @param string $deltaLogName
     * @param array $idKeys
     * @param int $pageNumber
     * @param int $pageSize
     * @param bool|false $getProcessed
     * @return array
     */
    public function loadDeletedRecords($deltaLogName, $idKeys, $pageNumber, $pageSize, $getProcessed = false);

    /**
     * Updates document records with specified data or insert if this is a new record
     *
     * @param mixed $document
     * @param array $data
     * @param bool|array $updateOnDuplicate
     * @return int
     */
    public function updateChangedRecords($document, $data, $updateOnDuplicate = false);

    /**
     * Backup document
     *
     * @param string $documentName
     * @return void
     */
    public function backupDocument($documentName);

    /**
     * Rollback document
     *
     * @param string $documentName
     * @return void
     */
    public function rollbackDocument($documentName);

    /**
     * Delete document backup
     *
     * @param string $documentName
     * @return void
     */
    public function deleteBackup($documentName);

    /**
     * Create delta for specified tables
     *
     * @param string $documentName
     * @param string $deltaLogName
     * @param string $idKey
     * @return boolean
     */
    public function createDelta($documentName, $deltaLogName, $idKey);

    /**
     * IInsert from select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $table
     * @param array $fields
     * @param bool|false $mode
     * @return mixed
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select, $table, array $fields = [], $mode = false);
}
