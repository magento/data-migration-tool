<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\Resource;

use Migration\Resource\Document;

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
     * @return array
     */
    public function loadPage($documentName, $pageNumber, $pageSize);

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
     * @param string $changeLogName
     * @param string $idKey
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     */
    public function loadChangedRecords($documentName, $changeLogName, $idKey, $pageNumber, $pageSize);

    /**
     * Load page with changed records from the document
     *
     * @param string $changeLogName
     * @param string $idKey
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     */
    public function loadDeletedRecords($changeLogName, $idKey, $pageNumber, $pageSize);

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
     * @param string $changeLogName
     * @param string $idKey
     * @return void
     */
    public function createDelta($documentName, $changeLogName, $idKey);
}
