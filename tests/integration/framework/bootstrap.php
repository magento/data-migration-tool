<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/autoload.php';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/tmp');
}
require BP . '/app/functions.php';

if (is_dir(TESTS_TEMP_DIR)) {
    $filesystemAdapter = new \Magento\Framework\Filesystem\Driver\File();
    $filesystemAdapter->deleteDirectory(TESTS_TEMP_DIR);
}
mkdir(TESTS_TEMP_DIR);

\Magento\Framework\Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());

set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('UTC');
