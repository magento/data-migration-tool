<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Resource;

/**
 * Resource destination class
 */
class Destination extends AbstractResource
{
    /**
     * Save data into destination resource
     *
     * @param string $documentName
     * @param \Migration\Resource\Record\RecordIteratorInterface $records
     * @return $this
     */
    public function saveRecords($documentName, $records)
    {
        $pageSize = $this->configReader->getOption('bulk_size');
        $i = 0;
        $data = [];
        foreach($records as $row) {
            $i++;
            $data[] = $row;
            if ($i == $pageSize) {
                $this->adapter->insertRecords($documentName, $data);
                $data = [];
                $i = 0;
            }
        }
        if ($i > 0) {
            $this->adapter->insertRecords($documentName, $data);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function getResourceConfig()
    {
        $destination = $this->configReader->getDestination();
        $config['host'] = $destination['database']['host'];
        $config['dbname'] = $destination['database']['name'];
        $config['username'] = $destination['database']['user'];
        $config['password'] = !empty($destination['database']['password'])
            ? $destination['database']['password']
            : '';
        return $config;
    }
}
