<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

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
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var \Migration\Config
     */
    protected $configReader;

    /**
     * @var \Migration\Resource\DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var \Migration\Resource\StructureFactory
     */
    protected $structureFactory;

    /**
     * @var \Migration\Resource\Document\Collection
     */
    protected $documentCollection;

    /**
     * @var array
     */
    protected $documentList;

    /**
     * @param AdapterFactory $adapterFactory
     * @param \Migration\Config $configReader
     * @param DocumentFactory $documentFactory
     * @param StructureFactory $structureFactory
     * @param Document\Collection $documentCollection
     */
    public function __construct(
        \Migration\Resource\AdapterFactory $adapterFactory,
        \Migration\Config $configReader,
        \Migration\Resource\DocumentFactory $documentFactory,
        \Migration\Resource\StructureFactory $structureFactory,
        \Migration\Resource\Document\Collection $documentCollection
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
     * @return \Migration\Resource\Document|false
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
     * @return \Migration\Resource\Structure
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
     * @return int
     */
    public function getRecordsCount($documentName)
    {
        return $this->adapter->getRecordsCount($this->addDocumentPrefix($documentName));
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
     * @return int
     */
    public function getPageSize()
    {
        $pageSize = (int)$this->configReader->getOption('bulk_size');
        return empty($pageSize) ? self::DEFAULT_BULK_SIZE : $pageSize;
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
        $pageSize = $pageSize ?: $this->getPageSize() ;
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
     * Get Resource Adapter
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
