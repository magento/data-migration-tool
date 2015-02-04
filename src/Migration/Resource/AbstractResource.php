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
        $data = $this->adapter->getDocumentStructure($documentName);
        return $this->structureFactory->create(['documentName' => $documentName, 'data' => $data]);
    }

    /**
     * Returns document list
     *
     * @return array
     */
    public function getDocumentList()
    {
        return $this->adapter->getDocumentList();
    }

    /**
     * Returns number of records of document
     *
     * @param string $documentName
     * @return int
     */
    public function getRecordsCount($documentName)
    {
        return $this->adapter->getRecordsCount($documentName);
    }

    /**
     * Returns configuration data for resource initialization
     *
     * @return array
     */
    abstract protected function getResourceConfig();
}
