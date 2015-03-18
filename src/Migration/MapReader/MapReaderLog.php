<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Migration\MapReader;

use Migration\Config;

/**
 * Class MapReaderLog
 */
class MapReaderLog extends MapReaderAbstract
{
    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
        $this->init($this->config->getOption('log_map_file'));
    }

    // @codeCoverageIgnoreStart

    /**
     * EAV tables mapping
     *
     * @return array
     */
    public function getDestDocumentsToClear()
    {
        return [
            'log_customer',
            'log_quote',
            'log_summary',
            'log_summary_type' ,
            'log_url',
            'log_url_info',
            'log_visitor',
            'log_visitor_info',
            'log_visitor_online'
        ];
    }

    /**
     * Documents mapping
     *
     * @return array
     */
    public function getDocumentList()
    {
        return ['log_visitor' => 'customer_visitor'];
    }
    // @codeCoverageIgnoreEnd
}
