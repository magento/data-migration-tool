<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Resource source class
 */
class Source extends AbstractResource
{
    const CONFIG_DOCUMENT_PREFIX = 'source_prefix';

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
        return $this->adapter->loadPage($documentName, $pageNumber, $this->getPageSize());
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
            $this->getPageSize(),
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
            $this->getPageSize(),
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
