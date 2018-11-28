<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Design\Theme\ThemePackageList;
use Magento\Framework\View\Design\Theme\ThemePackageFactory;

require __DIR__ . '/autoload.php';

if (!defined('TESTS_TEMP_DIR')) {
    define('TESTS_TEMP_DIR', dirname(__DIR__) . '/report');
}
if (is_dir(TESTS_TEMP_DIR)) {
    $filesystemAdapter = new File();
    $filesystemAdapter->deleteDirectory(TESTS_TEMP_DIR);
}
mkdir(TESTS_TEMP_DIR);

$componentRegistrar = new ComponentRegistrar();
$dirSearch = new DirSearch($componentRegistrar, new ReadFactory(new DriverPool()));
$themePackageList = new ThemePackageList($componentRegistrar, new ThemePackageFactory());
$serializer = new \Magento\Framework\Serialize\Serializer\Json();
$regexIteratorFactory = new Magento\Framework\App\Utility\RegexIteratorFactory();
\Magento\Framework\App\Utility\Files::setInstance(
    new Files($componentRegistrar, $dirSearch, $themePackageList, $serializer, $regexIteratorFactory)
);

error_reporting(E_ALL);
ini_set('display_errors', 1);
