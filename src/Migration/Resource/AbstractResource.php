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
     * @var \Migration\Resource\Document\DocumentFactory
     */
    protected $documentFactory;

    /**
     * @param \Migration\Resource\AdapterFactory $adapterFactory
     * @param \Migration\Config $configReader
     * @param Document\DocumentFactory $documentFactory
     */
    public function __construct(
        \Migration\Resource\AdapterFactory $adapterFactory,
        \Migration\Config $configReader,
        \Migration\Resource\Document\DocumentFactory $documentFactory
    ) {
        $this->configReader = $configReader;
        $this->adapter = $adapterFactory->create(['config' => $this->getResourceConfig()]);
        $this->documentFactory = $documentFactory;
    }

    /**
     * @param $documentName
     * @return \Migration\Resource\Document\Document
     */
    public function getDocument($documentName)
    {
        return $this->documentFactory->create(['documentName' => $documentName]);
    }

    /**
     * @param string $documentName
     * @return Record\RecordIteratorInterface
     */
    public function getRecords($documentName)
    {
        $records = $this->getDocument($documentName)->getRecordIterator();
        $records->setRecordProvider($this->adapter)
            ->setPageSize($this->configReader->getOption('bulk_size'));
        return $records;
    }

    /**
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
