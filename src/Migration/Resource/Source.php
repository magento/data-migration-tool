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
     * @param string $changeLogName
     * @param string $idKey
     * @return void
     */
    public function createDelta($documentName, $changeLogName, $idKey)
    {
        $this->adapter->createDelta($documentName, $changeLogName, $idKey);
    }

    /**
     * Get delta records from document
     *
     * @param string $documentName
     * @param string $idKey
     * @return array
     */
    public function getChangedRecords($documentName, $idKey)
    {
        return $this->adapter->loadChanges(
            $documentName,
            $this->getChangeLogName($documentName),
            $idKey,
            0,
            $this->getPageSize()
        );
    }
}
