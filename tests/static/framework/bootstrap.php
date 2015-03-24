<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/autoload.php';

\Magento\Framework\App\Utility\Files::setInstance(new \Magento\Framework\App\Utility\Files(realpath('../..')));

error_reporting(E_ALL);
ini_set('display_errors', 1);
