<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @return int
     */
    public function getRecordsCount($documentName);

    /**
     * Load Page
     *
     * @param string $documentName
     * @param int $pageNumber
     * @param int $pageSize
     * @param string $identityField
     * @param int $identityId
     * @return array
     */
    public function loadPage($documentName, $pageNumber, $pageSize, $identityField = null, $identityId = null);

    /**
     * Insert records into document
     *
     * @param string $documentName
     * @param array $records
     * @param bool $updateOnDuplicate
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
     * @param string $idKey
     * @param array $ids
     * @return void
     */
    public function deleteRecords($documentName, $idKey, $ids);

    /**
     * Load page with changed records from the document
     *
     * @param string $documentName
     * @param string $deltaLogName
     * @param string $idKey
     * @param int $pageNumber
     * @param int $pageSize
     * @param bool|false $getProcessed
     * @return array
     */
    public function loadChangedRecords(
        $documentName,
        $deltaLogName,
        $idKey,
        $pageNumber,
        $pageSize,
        $getProcessed = false
    );

    /**
     * Load page with changed records from the document
     *
     * @param string $deltaLogName
     * @param string $idKey
     * @param int $pageNumber
     * @param int $pageSize
     * @param bool|false $getProcessed
     * @return array
     */
    public function loadDeletedRecords($deltaLogName, $idKey, $pageNumber, $pageSize, $getProcessed = false);

    /**
     * Updates document records with specified data or insert if this is a new record
     *
     * @param mixed $document
     * @param array $data
     * @return int
     */
    public function updateChangedRecords($document, $data);

    /**
     * @param string $documentName
     * @return void
     */
    public function backupDocument($documentName);

    /**
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
     * @return void
     */
    public function createDelta($documentName, $deltaLogName, $idKey);

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param string $table
     * @param array $fields
     * @param bool|false $mode
     * @return mixed
     */
    public function insertFromSelect(\Magento\Framework\DB\Select $select, $table, array $fields = [], $mode = false);
}
