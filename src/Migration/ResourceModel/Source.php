<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

use \Migration\Config;

/**
 * ResourceModel source class
 */
class Source extends AbstractResource
{
    const CONFIG_DOCUMENT_PREFIX = 'source_prefix';

    /**
     * @var array
     */
    protected $documentIdentity = [];

    /**
     * @var array
     */
    protected $lastLoadedIdentityId = [];

    /**
     * @var string
     */
    protected $documentPrefix;

    /**
     * @inheritdoc
     */
    protected function getDocumentPrefix()
    {
        if (null === $this->documentPrefix) {
            $this->documentPrefix = $this->configReader->getOption(self::CONFIG_DOCUMENT_PREFIX);
        }
        return $this->documentPrefix;
    }

    /**
     * Load page
     *
     * @param string $documentName
     * @param int $pageNumber
     * @return array
     */
    public function loadPage($documentName, $pageNumber)
    {
        return $this->getAdapter()->loadPage($documentName, $pageNumber, $this->getPageSize($documentName));
    }

    /**
     * Return records from the document, paging is included
     *
     * @param string $documentName
     * @param int $pageNumber
     * @param int $pageSize
     * @param \Zend_Db_Expr $condition
     * @return array
     */
    public function getRecords($documentName, $pageNumber, $pageSize = null, \Zend_Db_Expr $condition = null)
    {
        $pageSize = $pageSize ?: $this->getPageSize($documentName) ;
        $identityField = $this->getIdentityField($documentName);
        $identityId = null;
        if ($identityField) {
            if (!isset($this->lastLoadedIdentityId[$documentName]) && $pageNumber == 0) {
                $identityId = 0;
            }
            if (isset($this->lastLoadedIdentityId[$documentName])) {
                $identityId = $this->lastLoadedIdentityId[$documentName];
                if ($identityId == 0) {
                    $this->lastLoadedIdentityId[$documentName] = ++$identityId;
                }
            }
        }

        $records = $this->getAdapter()->loadPage(
            $this->addDocumentPrefix($documentName),
            $pageNumber,
            $pageSize,
            $identityField,
            $identityId,
            $condition
        );

        return $records;
    }

    /**
     * Set last loaded record
     *
     * @param string $documentName
     * @param array $record
     * @return void
     */
    public function setLastLoadedRecord($documentName, array $record)
    {
        $identityField = $this->getIdentityField($documentName);
        if ($identityField && isset($record[$identityField])) {
            $this->lastLoadedIdentityId[$documentName] = $record[$identityField];
        } elseif (empty($record)) {
            unset($this->lastLoadedIdentityId[$documentName]);
        }
    }

    /**
     * Get identity field
     *
     * @param string $documentName
     * @return mixed
     */
    protected function getIdentityField($documentName)
    {
        if (array_key_exists($documentName, $this->documentIdentity)) {
            return $this->documentIdentity[$documentName];
        }

        $this->documentIdentity[$documentName] = null;
        foreach ($this->getDocument($documentName)->getStructure()->getFields() as $params) {
            if ($params['PRIMARY'] && $params['IDENTITY']) {
                $this->documentIdentity[$documentName] = $params['COLUMN_NAME'];
            }
        }

        return $this->documentIdentity[$documentName];
    }

    /**
     * Create delta for specified table
     *
     * @param string $documentName
     * @param string $idKey
     * @return boolean
     */
    public function createDelta($documentName, $idKey)
    {
        $result = $this->getAdapter()->createDelta(
            $this->addDocumentPrefix($documentName),
            $this->addDocumentPrefix($this->getDeltaLogName($documentName)),
            $idKey
        );
        return $result;
    }

    /**
     * Get changed records for document
     *
     * @param string $documentName
     * @param array $idKeys
     * @param int $pageNumber
     * @param bool|false $getProcessed
     * @return array
     */
    public function getChangedRecords($documentName, $idKeys, $pageNumber = 0, $getProcessed = false)
    {
        return $this->getAdapter()->loadChangedRecords(
            $this->addDocumentPrefix($documentName),
            $this->addDocumentPrefix($this->getDeltaLogName($documentName)),
            $idKeys,
            $pageNumber,
            $this->getPageSize($documentName),
            $getProcessed
        );
    }

    /**
     * Get deleted records for document
     *
     * @param string $documentName
     * @param array $idKeys
     * @param int $pageNumber
     * @param bool|false $getProcessed
     * @return array
     */
    public function getDeletedRecords($documentName, $idKeys, $pageNumber = 0, $getProcessed = false)
    {
        return $this->getAdapter()->loadDeletedRecords(
            $this->addDocumentPrefix($this->getDeltaLogName($documentName)),
            $idKeys,
            $pageNumber,
            $this->getPageSize($documentName),
            $getProcessed
        );
    }

    /**
     * Get delta log name
     *
     * @param string $documentName
     * @return string
     */
    public function getDeltaLogName($documentName)
    {
        $maximumNameLength = 64;
        $documentName = 'm2_cl_' . $documentName;

        if (strlen($documentName) > $maximumNameLength) {
            $documentName = substr($documentName, 0, $maximumNameLength);
        }

        return $documentName;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return Config::RESOURCE_TYPE_SOURCE;
    }
}
