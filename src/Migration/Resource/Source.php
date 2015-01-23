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
    /**
     * Returns next bunch of data
     *
     * @return array
     */
    public function getNextBunch()
    {
        if (!$this->resourceUnitName) {
            throw new \InvalidArgumentException('Resource name is not set');
        }
        $select = $this->resourceAdapter->select();
        $select->from($this->resourceUnitName, '*')
            ->limit($this->bulkSize, $this->getPosition());
        $bunch = $this->resourceAdapter->fetchAll($select);
        $this->setPosition($this->bulkSize + $this->getPosition());
        return $bunch;
    }

    /**
     * @inheritdoc
     */
    protected function getResourceConfig(\Migration\Config $configReader)
    {
        $source = $configReader->getSource();
        $config['host'] = $source['database']['host'];
        $config['dbname'] = $source['database']['name'];
        $config['username'] = $source['database']['user'];
        $config['password'] = !empty($source['database']['password'])
            ? $source['database']['password']
            : '';
        return $config;
    }
}
