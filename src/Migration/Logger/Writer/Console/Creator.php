<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Migration\Logger\Writer\Console;

use Zend\Console\Console as ZendConsole;

class Creator
{
    /**
     * @return \Zend\Console\Adapter\AdapterInterface
     */
    public function create()
    {
        return ZendConsole::getInstance();
    }
}
