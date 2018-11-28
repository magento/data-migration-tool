<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\ResourceModel;

use \Migration\Config;

/**
 * ResourceModel destination class
 */
class Destination extends AbstractResource
{
    const CONFIG_DOCUMENT_PREFIX = 'dest_prefix';

    /**
     * @var string
     */
    protected $documentPrefix;

    /**
     * Save data into destination resource
     *
     * @param string $documentName
     * @param \Migration\ResourceModel\Record\Collection|array $records
     * @param bool|array $updateOnDuplicate
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
                $this->getAdapter()->insertRecords($documentName, $data, $updateOnDuplicate);
                $data = [];
                $i = 0;
            }
        }
        if ($i > 0) {
            $this->getAdapter()->insertRecords($documentName, $data, $updateOnDuplicate);
        }
        return $this;
    }

    /**
     * Clear document
     *
     * @param string $documentName
     * @return void
     */
    public function clearDocument($documentName)
    {
        $this->getAdapter()->deleteAllRecords($this->addDocumentPrefix($documentName));
    }

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
     * Backup document
     *
     * @param string $documentName
     * @return void
     */
    public function backupDocument($documentName)
    {
        $this->getAdapter()->backupDocument($this->addDocumentPrefix($documentName));
    }

    /**
     * Rollback document
     *
     * @param string $documentName
     * @return void
     */
    public function rollbackDocument($documentName)
    {
        $this->getAdapter()->rollbackDocument($this->addDocumentPrefix($documentName));
    }

    /**
     * Delete document backup
     *
     * @param string $documentName
     * @return void
     */
    public function deleteDocumentBackup($documentName)
    {
        $this->getAdapter()->deleteBackup($this->addDocumentPrefix($documentName));
    }

    /**
     * Update changed records
     *
     * @param string $documentName
     * @param \Migration\ResourceModel\Record\Collection $records
     * @param array|bool $updateOnDuplicate
     * @return void
     */
    public function updateChangedRecords($documentName, $records, $updateOnDuplicate = false)
    {
        $documentName = $this->addDocumentPrefix($documentName);
        $data = [];
        /** @var \Migration\ResourceModel\Record $row */
        foreach ($records as $row) {
            $data[] = $row->getData();
        }
        if (!empty($data)) {
            $this->getAdapter()->updateChangedRecords(
                $this->addDocumentPrefix($documentName),
                $data,
                $updateOnDuplicate
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return Config::RESOURCE_TYPE_DESTINATION;
    }
}
