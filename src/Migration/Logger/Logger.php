<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger;

/**
 * Processing logger handler creation for migration application
 */
class Logger extends \Monolog\Logger
{
    /**
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct($name = 'Migration', array $handlers = [], array $processors = [])
    {
        parent::__construct($name, $handlers, $processors);
    }
}
