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
     * @param AdapterFactory $adapterFactory
     * @param \Migration\Config $configReader
     * @param DocumentFactory $documentFactory
     * @param StructureFactory $structureFactory
     */
    public function __construct(
        \Migration\Resource\AdapterFactory $adapterFactory,
        \Migration\Config $configReader,
        \Migration\Resource\DocumentFactory $documentFactory,
        \Migration\Resource\StructureFactory $structureFactory
    ) {
        $this->configReader = $configReader;
        $this->adapter = $adapterFactory->create(['config' => $this->getResourceConfig()]);
        $this->documentFactory = $documentFactory;
        $this->structureFactory = $structureFactory;
    }

    /**
     * Returns document object
     *
     * @param string $documentName
     * @return \Migration\Resource\Document
     */
    public function getDocument($documentName)
    {
        $structure = $this->getStructure($documentName);
        return $this->documentFactory->create(['structure' => $structure, 'documentName' => $documentName]);
    }

    /**
     * Returns document object
     *
     * @param string $documentName
     * @return \Migration\Resource\Document
     */
    public function getStructure($documentName)
    {
        $data = $this->adapter->getDocumentStructure($documentName);
        return $this->structureFactory->create(['documentName' => $documentName, 'data' => $data]);
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
