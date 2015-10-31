<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

/**
 * Abstract class for source and destination classes
 */
abstract class AbstractResource
{
    /**
     * Default bulk size if not set in config
     */
    const DEFAULT_BULK_SIZE = 100;

    /**
     * Max bulk size allowed
     */
    const MAX_BULK_SIZE = 50000;

    /**
     * Empirically detected average memory size required for 1 column
     */
    const MEMORY_PER_FIELD = 3000;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @var \Migration\ResourceModel\DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var \Migration\ResourceModel\StructureFactory
     */
    protected $structureFactory;

    /**
     * @var \Migration\ResourceModel\Document\Collection
     */
    protected $documentCollection;

    /**
     * @var array
     */
    protected $documentList;

    /**
     * @var array
     */
    protected $documentBulkSize = [];

    /**
     * @param AdapterFactory $adapterFactory
     * @param \Migration\Config $configReader
     * @param DocumentFactory $documentFactory
     * @param StructureFactory $structureFactory
     * @param Document\Collection $documentCollection
     */
    public function __construct(
        \Migration\ResourceModel\AdapterFactory $adapterFactory,
        \Migration\Config $configReader,
        \Migration\ResourceModel\DocumentFactory $documentFactory,
        \Migration\ResourceModel\StructureFactory $structureFactory,
        \Migration\ResourceModel\Document\Collection $documentCollection
    ) {
        $this->configReader = $configReader;
        $this->adapter = $adapterFactory->create(['config' => $this->getResourceConfig()]);
        $this->documentFactory = $documentFactory;
        $this->structureFactory = $structureFactory;
        $this->documentCollection = $documentCollection;
    }

    /**
     * Returns document object
     *
     * @param string $documentName
     * @return \Migration\ResourceModel\Document|false
     */
    public function getDocument($documentName)
    {
        $documentList = $this->getDocumentList();
        if (!in_array($documentName, $documentList)) {
            return false;
        }
        $structure = $this->getStructure($documentName);
        return $this->documentFactory->create(['structure' => $structure, 'documentName' => $documentName]);
    }

    /**
     * Returns document object
     *
     * @param string $documentName
     * @return \Migration\ResourceModel\Structure
     */
    public function getStructure($documentName)
    {
        $data = $this->adapter->getDocumentStructure($this->addDocumentPrefix($documentName));
        return $this->structureFactory->create(['documentName' => $documentName, 'data' => $data]);
    }

    /**
     * Returns document list
     *
     * @return array
     */
    public function getDocumentList()
    {
        $this->documentList = $this->adapter->getDocumentList();
        foreach ($this->documentList as &$documentName) {
            $documentName = $this->removeDocumentPrefix($documentName);
        }
        return $this->documentList;
    }

    /**
     * Returns number of records of document
     *
     * @param string $documentName
     * @param bool $usePrefix
     * @return int
     */
    public function getRecordsCount($documentName, $usePrefix = true)
    {
        return $this->adapter->getRecordsCount($usePrefix ? $this->addDocumentPrefix($documentName) : $documentName);
    }

    /**
     * Remove prefix from document name
     *
     * @param string $documentName
     * @return string
     */
    protected function removeDocumentPrefix($documentName)
    {
        $prefix = $this->getDocumentPrefix();
        if (!empty($prefix) && (strpos($documentName, $prefix) === 0)) {
            $documentName = substr($documentName, strlen($prefix));
        }
        return $documentName;
    }

    /**
     * Add prefix for document name
     *
     * @param string $documentName
     * @return string
     */
    public function addDocumentPrefix($documentName)
    {
        $prefix = $this->getDocumentPrefix();
        if (!empty($prefix) && (strpos($documentName, $prefix) !== 0)) {
            $documentName = $prefix . $documentName;
        }
        return $documentName;
    }

    /**
     * Retrieving bulk size
     *
     * @param string $documentName
     * @return mixed
     */
    public function getPageSize($documentName)
    {
        if (array_key_exists($documentName, $this->documentBulkSize)) {
            return $this->documentBulkSize[$documentName];
        }

        $configValue = (int)$this->configReader->getOption('bulk_size');
        if ($configValue === 0) {
            $fields = $this->getDocument($documentName)->getStructure()->getFields();
            $fieldsNumber = count($fields);
            $iniMemoryLimit = ini_get('memory_limit');
            if ($iniMemoryLimit > 0) {
                $memoryLimit = $this->getBytes($iniMemoryLimit);
                $pageSize = ceil($memoryLimit / (self::MEMORY_PER_FIELD * $fieldsNumber));
            } else {
                $pageSize = self::MAX_BULK_SIZE;
            }
        } else {
            $pageSize = $configValue > 0 ? $configValue : self::DEFAULT_BULK_SIZE;
        }

        $pageSize = $pageSize > self::MAX_BULK_SIZE ? self::MAX_BULK_SIZE : $pageSize;
        $this->documentBulkSize[$documentName] = $pageSize;

        return $this->documentBulkSize[$documentName];
    }

    /**
     * @param string $memoryLimit
     * @return int|string
     */
    protected function getBytes($memoryLimit)
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit)-1]);
        switch($last) {
            case 'g':
                $memoryLimit *= 1024;
                // fall-through intentional
            case 'm':
                $memoryLimit *= 1024;
                // fall-through intentional
            case 'k':
                $memoryLimit *= 1024;
                break;
        }

        return $memoryLimit;
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
        return $this->adapter->loadPage($this->addDocumentPrefix($documentName), $pageNumber, $pageSize);
    }

    /**
     * Delete multiple records from document
     *
     * @param string $documentName
     * @param string $idKey
     * @param [] $ids
     * @return void
     */
    public function deleteRecords($documentName, $idKey, $ids)
    {
        $this->adapter->deleteRecords($documentName, $idKey, $ids);
    }

    /**
     * Get ResourceModel Adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Returns configuration data for resource initialization
     *
     * @return array
     */
    abstract protected function getResourceConfig();

    /**
     * Returns configuration data for documents prefix
     *
     * @return null|string
     */
    abstract protected function getDocumentPrefix();
}
