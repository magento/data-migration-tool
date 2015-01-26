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
     * @inheritdoc
     */
    protected function getResourceConfig()
    {
        $source = $this->configReader->getSource();
        $config['host'] = $source['database']['host'];
        $config['dbname'] = $source['database']['name'];
        $config['username'] = $source['database']['user'];
        $config['password'] = !empty($source['database']['password'])
            ? $source['database']['password']
            : '';
        return $config;
    }
}
