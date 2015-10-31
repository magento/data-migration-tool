<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

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
     * {@inheritdoc}
     */
    protected function getResourceConfig()
    {
        $source = $this->configReader->getSource();
        $sourceType = $source['type'];
        $config['host'] = $source[$sourceType]['host'];
        $config['dbname'] = $source[$sourceType]['name'];
        $config['username'] = $source[$sourceType]['user'];
        $config['password'] = !empty($source[$sourceType]['password'])
            ? $source[$sourceType]['password']
            : '';
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDocumentPrefix()
    {
        return $this->configReader->getOption(self::CONFIG_DOCUMENT_PREFIX);
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
        return $this->adapter->loadPage($documentName, $pageNumber, $this->getPageSize($documentName));
    }

    /**
     * Return records from the document, paging is included
     *
     * @param string $documentName
     * @param int $pageNumber
     * @param int $pageSize
     * @return array
     */
    public function getRecords($documentName, $pageNumber, $pageSize = null)
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
            }
        }

        $records = $this->adapter->loadPage(
            $this->addDocumentPrefix($documentName),
            $pageNumber,
            $pageSize,
            $identityField,
            $identityId
        );

        return $records;
    }

    /**
     * @param string $documentName
     * @param array $record
     * @return void
     */
    public function setLastLoadedRecord($documentName, array $record)
    {
        if ($this->getIdentityField($documentName) && isset($record[$this->getIdentityField($documentName)])) {
            $this->lastLoadedIdentityId[$documentName] = $record[$this->getIdentityField($documentName)];
        } elseif (empty($record)) {
            unset($this->lastLoadedIdentityId[$documentName]);
        }
    }

    /**
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
     * @return void
     */
    public function createDelta($documentName, $idKey)
    {
        $this->adapter->createDelta(
            $this->addDocumentPrefix($documentName),
            $this->addDocumentPrefix($this->getDeltaLogName($documentName)),
            $idKey
        );
    }

    /**
     * Get changed records for document
     *
     * @param string $documentName
     * @param string $idKey
     * @param int $pageNumber
     * @param bool|false $getProcessed
     * @return array
     */
    public function getChangedRecords($documentName, $idKey, $pageNumber = 0, $getProcessed = false)
    {
        return $this->adapter->loadChangedRecords(
            $this->addDocumentPrefix($documentName),
            $this->addDocumentPrefix($this->getDeltaLogName($documentName)),
            $idKey,
            $pageNumber,
            $this->getPageSize($documentName),
            $getProcessed
        );
    }

    /**
     * Get deleted records for document
     *
     * @param string $documentName
     * @param string $idKey
     * @param bool|false $getProcessed
     * @return array
     */
    public function getDeletedRecords($documentName, $idKey, $getProcessed = false)
    {
        return $this->adapter->loadDeletedRecords(
            $this->addDocumentPrefix($this->getDeltaLogName($documentName)),
            $idKey,
            0,
            $this->getPageSize($documentName),
            $getProcessed
        );
    }

    /**
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
}
