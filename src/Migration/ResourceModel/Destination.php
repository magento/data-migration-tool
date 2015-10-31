<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

/**
 * ResourceModel destination class
 */
class Destination extends AbstractResource
{
    const CONFIG_DOCUMENT_PREFIX = 'dest_prefix';

    /**
     * Save data into destination resource
     *
     * @param string $documentName
     * @param \Migration\ResourceModel\Record\Collection|array $records
     * @param bool $updateOnDuplicate
     * @return $this
     */
    public function saveRecords($documentName, $records, $updateOnDuplicate = false)
    {
        $pageSize = $this->getPageSize($documentName);
        $i = 0;
        $data = [];
        $documentName = $this->addDocumentPrefix($documentName);
        /** @var \Migration\ResourceModel\Record|array $row */
        foreach ($records as $row) {
            $i++;
            if ($row instanceof \Migration\ResourceModel\Record) {
                $data[] = $row->getData();
            } else {
                $data[] = $row;
            }
            if ($i == $pageSize) {
                $this->adapter->insertRecords($documentName, $data, $updateOnDuplicate);
                $data = [];
                $i = 0;
            }
        }
        if ($i > 0) {
            $this->adapter->insertRecords($documentName, $data, $updateOnDuplicate);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getResourceConfig()
    {
        $destination = $this->configReader->getDestination();
        $destinationType = $destination['type'];
        $config['host'] = $destination[$destinationType]['host'];
        $config['dbname'] = $destination[$destinationType]['name'];
        $config['username'] = $destination[$destinationType]['user'];
        $config['password'] = !empty($destination[$destinationType]['password'])
            ? $destination[$destinationType]['password']
            : '';
        return $config;
    }

    /**
     * @param string $documentName
     * @return void
     */
    public function clearDocument($documentName)
    {
        $this->adapter->deleteAllRecords($this->addDocumentPrefix($documentName));
    }

    /**
     * {@inheritdoc}
     */
    protected function getDocumentPrefix()
    {
        return $this->configReader->getOption(self::CONFIG_DOCUMENT_PREFIX);
    }

    /**
     * @param string $documentName
     * @return void
     */
    public function backupDocument($documentName)
    {
        $this->getAdapter()->backupDocument($this->addDocumentPrefix($documentName));
    }

    /**
     * @param string $documentName
     * @return void
     */
    public function rollbackDocument($documentName)
    {
        $this->getAdapter()->rollbackDocument($this->addDocumentPrefix($documentName));
    }

    /**
     * @param string $documentName
     * @return void
     */
    public function deleteDocumentBackup($documentName)
    {
        $this->getAdapter()->deleteBackup($this->addDocumentPrefix($documentName));
    }

    /**
     * @param string $documentName
     * @param \Migration\ResourceModel\Record\Collection $records
     * @return void
     */
    public function updateChangedRecords($documentName, $records)
    {
        $documentName = $this->addDocumentPrefix($documentName);
        $data = [];
        /** @var \Migration\ResourceModel\Record $row */
        foreach ($records as $row) {
            $data[] = $row->getData();
        }
        if (!empty($data)) {
            $this->adapter->updateChangedRecords($this->addDocumentPrefix($documentName), $data);
        }
    }
}
